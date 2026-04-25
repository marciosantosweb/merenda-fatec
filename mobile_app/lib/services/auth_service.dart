import 'dart:async';
import 'dart:convert';
import 'dart:math';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import 'package:app_links/app_links.dart';
import 'package:crypto/crypto.dart';
import 'database_helper.dart';

/// Credenciais Microsoft (espelho do config.php do servidor)
const _clientId   = '12154503-57fa-4498-8c9a-4e75c09abfe5';
const _tenantId   = 'common';
// Redirect URI registrada no Azure para aplicativos móveis (custom scheme)
const _redirectUri = 'msauth://com.example.rango/2xH+Zpa+eNoE3G8dG1mBYsFEU+w=';
const _scope      = 'openid profile User.Read offline_access';

/// URL base da API do servidor
const _apiBase = 'https://www.etecsaosebastiao.com.br/fatec/merenda/api';

/// Gera uma string aleatória para PKCE
String _generateCodeVerifier() {
  final random = Random.secure();
  final values = List<int>.generate(32, (i) => random.nextInt(256));
  return base64UrlEncode(values).replaceAll('=', '').replaceAll('+', '-').replaceAll('/', '_');
}

/// Gera o Code Challenge em S256 (Padrão ouro do Azure)
String _generateCodeChallenge(String verifier) {
  final bytes = utf8.encode(verifier);
  final digest = sha256.convert(bytes);
  return base64UrlEncode(digest.bytes).replaceAll('=', '').replaceAll('+', '-').replaceAll('/', '_');
}

class AuthService {
  final DatabaseHelper _db = DatabaseHelper();
  final _appLinks = AppLinks();
  StreamSubscription<Uri>? _linkSubscription;

  AuthService() {
    // Configura o listener de deep links silenciosamente ao iniciar o servico
    _appLinks.uriLinkStream.listen((uri) {}).cancel(); // Initialize stream
  }

  // ─── Verificar sessão local ────────────────────────────────────────────────

  Future<Map<String, dynamic>?> getLocalSession() async {
    final session = await _db.getSession();
    if (session == null) return null;

    final expiresAt = DateTime.tryParse(session['expires_at'] ?? '');
    if (expiresAt == null || DateTime.now().isAfter(expiresAt)) {
      await _db.clearSession();
      return null;
    }
    return session;
  }

  // ─── Login via Microsoft OAuth2 com Navegador Externo ────────────────────

  Future<String?> _loginWithMicrosoft() async {
    final codeVerifier = _generateCodeVerifier();
    final codeChallenge = _generateCodeChallenge(codeVerifier);
    final state = _generateCodeVerifier().substring(0, 16);
    final nonce = DateTime.now().microsecondsSinceEpoch.toString();

    final authUrl = Uri.parse(
        'https://login.microsoftonline.com/$_tenantId/oauth2/v2.0/authorize'
        '?client_id=$_clientId'
        '&response_type=code'
        '&redirect_uri=${Uri.encodeComponent(_redirectUri)}'
        '&scope=${Uri.encodeComponent(_scope)}'
        '&state=$state'
        '&nonce=$nonce'
        '&code_challenge=$codeChallenge'
        '&code_challenge_method=S256'
        '&prompt=select_account'
        '&login_hint=marcio.santos01@cps.sp.gov.br'
    );

    // Cria um Completer para aguardar a resposta via Deep Link
    final completer = Completer<String?>();

    // Escuta os links que o app receber
    _linkSubscription = _appLinks.uriLinkStream.listen((Uri? uri) async {
      if (uri != null && uri.scheme == 'msauth') {
        
        // A Azure pode devolver os parâmetros na query ou no fragment (#)
        final params = uri.queryParameters.isNotEmpty ? uri.queryParameters : Uri.splitQueryString(uri.fragment);
        
        final code = params['code'];
        final error = params['error'];
        final errorDesc = params['error_description'];

        if (code != null && !completer.isCompleted) {
          final token = await _exchangeCodeForToken(code, codeVerifier);
          completer.complete(token ?? 'ERRO_TROCA_TOKEN');
        } else if (error != null && !completer.isCompleted) {
          completer.complete('MS_ERROR: $error - $errorDesc');
        } else if (!completer.isCompleted) {
          completer.complete(null);
        }
      }
    });

    try {
      // Abre o Chrome VERDADEIRO externo, garantindo máxima confiabilidade.
      final launched = await launchUrl(
        authUrl,
        mode: LaunchMode.externalApplication,
      );

      if (!launched && !completer.isCompleted) {
         completer.complete(null);
         return null;
      }
    } catch (e) {
      if (!completer.isCompleted) completer.complete(null);
    }

    // Espera até 60 segundos pelo retorno do navegador
    final result = await completer.future.timeout(
      const Duration(seconds: 60),
      onTimeout: () => null,
    );
    
    // Limpa a escuta após retornar
    _linkSubscription?.cancel();
    
    return result;
  }

  Future<String?> _exchangeCodeForToken(String code, String codeVerifier) async {
    try {
      final response = await http.post(
        Uri.parse('https://login.microsoftonline.com/$_tenantId/oauth2/v2.0/token'),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: {
          'client_id': _clientId,
          'client_secret': 'hFm8Q~K.8DHNPSnpQXf3LUKDNUmqYo9BdMUk_djm', // Contingência: Forçando a aceitação caso o App no Azure seja Web
          'grant_type': 'authorization_code',
          'code': code,
          'redirect_uri': _redirectUri,
          'code_verifier': codeVerifier,
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['access_token'];
      }
      
      // Se falhar, retorna o erro exato da Azure
      return 'EXCHANGE_ERROR: ${response.statusCode} | ${response.body}';
    } catch (e) {
      return null;
    }
  }

  // ─── Validar token com a API PHP ──────────────────────────────────────────

  Future<Map<String, dynamic>> _validateWithServer(String accessToken) async {
    try {
      final response = await http.post(
        Uri.parse('$_apiBase/v1/auth_mobile.php'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'access_token': accessToken}),
      ).timeout(const Duration(seconds: 15));

      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Sem conexão com o servidor. Verifique sua internet.'};
    }
  }

  // ─── Fluxo principal de login ─────────────────────────────────────────────

  Future<Map<String, dynamic>> signIn() async {
    final accessToken = await _loginWithMicrosoft();
    
    if (accessToken == null) {
      return {'success': false, 'message': 'Login cancelado no navegador ou sem resposta da Microsoft.'};
    }
    
    if (accessToken.startsWith('MS_ERROR:')) {
      return {'success': false, 'message': 'A Microsoft bloqueou o login:\n$accessToken'};
    }
    
    if (accessToken.startsWith('EXCHANGE_ERROR:')) {
      return {'success': false, 'message': 'O código foi gerado, mas a Microsoft recusou a troca pelo Token. Motivo:\n$accessToken'};
    }
    
    if (accessToken == 'ERRO_TROCA_TOKEN') {
      return {'success': false, 'message': 'O código foi gerado, mas o servidor da Microsoft recusou a troca pelo Access Token.'};
    }

    final result = await _validateWithServer(accessToken);

    if (result['success'] == true) {
      final user = result['user'] as Map<String, dynamic>;
      final expiresAt = DateTime.now().add(const Duration(days: 30));
      await _db.saveSession({
        'id'            : user['id'],
        'name'          : user['name'],
        'email'         : user['email'],
        'role'          : user['role'],
        'microsoft_token': accessToken,
        'login_date'    : DateTime.now().toIso8601String(),
        'expires_at'    : expiresAt.toIso8601String(),
      });
    }

    return result;
  }

  Future<void> signOut() async {
    await _db.clearSession();
  }
}
