import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
import 'screens/splash_screen.dart';
import 'services/auth_service.dart';

void main() {
  runApp(const RangoApp());
}

class RangoApp extends StatelessWidget {
  const RangoApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Rango!',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFB50D11),
          primary: const Color(0xFFB50D11),
          secondary: const Color(0xFF313131),
        ),
        fontFamily: 'Roboto',
        useMaterial3: true,
      ),
      // Roteamento nomeado para facilitar o logout
      routes: {
        '/login': (_) => const LoginScreen(),
      },
      // Verifica sessão local e decide qual tela mostrar
      home: const SplashRouter(),
    );
  }
}

/// Tela de splash que verifica se há sessão ativa.
/// Redireciona para Login ou Home conforme o resultado.
class SplashRouter extends StatefulWidget {
  const SplashRouter({super.key});

  @override
  State<SplashRouter> createState() => _SplashRouterState();
}

class _SplashRouterState extends State<SplashRouter> {
  Map<String, dynamic>? _session;

  Future<void> _checkSession() async {
    _session = await AuthService().getLocalSession();
  }

  void _navigate() {
    if (!mounted) return;
    if (_session != null) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => HomeScreen(user: _session!)),
      );
    } else {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return SplashScreen(
      onComplete: () async {
        await _checkSession();
        _navigate();
      },
    );
  }
}
