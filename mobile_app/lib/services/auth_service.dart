import 'dart:convert';
import 'dart:math';
import 'package:flutter_web_auth_2/flutter_web_auth_2.dart';
import 'package:http/http.dart' as http;
import 'database_helper.dart';

/// Credenciais Microsoft (espelho do config.php do servidor)
const _clientId   = '12154503-57fa-4498-8c9a-4e75c09abfe5';
const _tenantId   = 'common';
// Redirect URI registrada no Azure para aplicativos móveis (custom scheme)
const _redirectUri = 'msauth://com.example.rango/2xH%2BZpa%2BeNoE3G8dG1mBYsFEU%2Bw%3D';
const _scope      = 'openid profile User.Read offline_access';

/// URL base da API do servidor
const _apiBase = 'https://www.etecsaosebastiao.com.br/fatec/merenda/api';

/// Gera uma string aleatória para PKCE
String _generateCodeVerifier() {
  final random = Random.secure();
  final values = List<int>.generate(64, (i) => random.nextInt(256));
  return base64UrlEncode(values).replaceAll('=', '');
}

class AuthService {
  final DatabaseHelper _db = DatabaseHelper();

  // ─── Verificar sessão local ────────────────────────────────────────────────

  /// Retorna o usuário salvo localmente se ainda não expirou (30 dias).
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

  // ─── Login via Microsoft OAuth2 com PKCE ─────────────────────────────────

  /// Abre o browser OAuth da Microsoft e retorna o access_token.
  Future<String?> _loginWithMicrosoft() async {
    // PKCE: Gera um code_verifier e usa como code_challenge
    // (sem transformação SHA256 pois usamos plain, mais simples e suportado)
    final codeVerifier = _generateCodeVerifier();
    final state = _generateCodeVerifier().substring(0, 16);

    final authUrl =
        'https://login.microsoftonline.com/$_tenantId/oauth2/v2.0/authorize'
        '?client_id=$_clientId'
        '&response_type=code'
        '&redirect_uri=${Uri.encodeComponent(_redirectUri)}'
        '&scope=${Uri.encodeComponent(_scope)}'
        '&state=$state'
        '&code_challenge=$codeVerifier'
        '&code_challenge_method=plain'
        '&prompt=select_account';

    try {
      final result = await FlutterWebAuth2.authenticate(
        url: authUrl,
        callbackUrlScheme: 'msauth',
        options: const FlutterWebAuth2Options(
          preferEphemeral: false,
        ),
      );

      final uri = Uri.parse(result);
      final code = uri.queryParameters['code'];

      if (code == null) return null;

      // Troca o code pelo access_token usando PKCE (sem client_secret)
      return await _exchangeCodeForToken(code, codeVerifier);
    } catch (e) {
      return null;
    }
  }

  /// Troca o authorization code pelo Access Token via PKCE (sem client_secret).
  Future<String?> _exchangeCodeForToken(String code, String codeVerifier) async {
    try {
      final response = await http.post(
        Uri.parse('https://login.microsoftonline.com/$_tenantId/oauth2/v2.0/token'),
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: {
          'client_id': _clientId,
          'grant_type': 'authorization_code',
          'code': code,
          'redirect_uri': _redirectUri,
          'scope': _scope,
          'code_verifier': codeVerifier,
        },
      ).timeout(const Duration(seconds: 30));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data['access_token'];
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  // ─── Validar token com a API PHP ──────────────────────────────────────────

  /// Envia o access_token da Microsoft para a API, que valida e retorna o perfil.
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

  /// Executa o fluxo completo: OAuth Microsoft → validação API → salva sessão.
  Future<Map<String, dynamic>> signIn() async {
    // 1. Abre autenticação Microsoft
    final accessToken = await _loginWithMicrosoft();
    if (accessToken == null) {
      return {'success': false, 'message': 'Login cancelado pelo usuário.'};
    }

    // 2. Valida com servidor PHP e verifica status no banco
    final result = await _validateWithServer(accessToken);

    if (result['success'] == true) {
      // 3. Salva sessão local por 30 dias
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

  // ─── Logout ───────────────────────────────────────────────────────────────

  Future<void> signOut() async {
    await _db.clearSession();
  }
}
