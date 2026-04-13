import 'dart:convert';
import 'package:flutter_web_auth_2/flutter_web_auth_2.dart';
import 'package:http/http.dart' as http;
import 'database_helper.dart';

/// Credenciais Microsoft (espelho do config.php do servidor)
const _clientId   = '12154503-57fa-4498-8c9a-4e75c09abfe5';
const _tenantId   = 'common';
// Redirect URI registrada no Azure para aplicativos móveis (custom scheme)
const _redirectUri = 'msauth.com.rango.fatec://auth';
const _scope      = 'openid profile User.Read';

/// URL base da API do servidor
const _apiBase = 'https://www.etecsaosebastiao.com.br/fatec/merenda/api';

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

  // ─── Login via Microsoft OAuth2 ──────────────────────────────────────────

  /// Abre o browser OAuth da Microsoft e retorna o access_token.
  Future<String?> _loginWithMicrosoft() async {
    final authUrl =
        'https://login.microsoftonline.com/$_tenantId/oauth2/v2.0/authorize'
        '?client_id=$_clientId'
        '&response_type=token'
        '&redirect_uri=${Uri.encodeComponent(_redirectUri)}'
        '&scope=${Uri.encodeComponent(_scope)}'
        '&response_mode=fragment';

    try {
      final result = await FlutterWebAuth2.authenticate(
        url: authUrl,
        callbackUrlScheme: 'msauth.com.rango.fatec',
      );

      final uri = Uri.parse(result.replaceFirst('#', '?'));
      return uri.queryParameters['access_token'];
    } catch (e) {
      return null; // usuário cancelou
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
  /// Retorna um Map com:
  ///   - 'success': bool
  ///   - 'blocked': bool (opcional)
  ///   - 'message': String (em caso de erro)
  ///   - 'user': Map (em caso de sucesso)
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
