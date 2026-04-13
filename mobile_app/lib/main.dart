import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/home_screen.dart';
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
  @override
  void initState() {
    super.initState();
    _checkSession();
  }

  Future<void> _checkSession() async {
    final session = await AuthService().getLocalSession();

    if (!mounted) return;

    if (session != null) {
      // Sessão válida (dentro de 30 dias): vai direto para a home
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => HomeScreen(user: session),
        ),
      );
    } else {
      // Sem sessão ou expirada: pede login
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    // Splash minimalista enquanto verifica a sessão
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset(
              'assets/images/logo-rango.png',
              height: 100,
              errorBuilder: (_, __, ___) => const Icon(
                Icons.restaurant_menu,
                color: Color(0xFFB50D11),
                size: 80,
              ),
            ),
            const SizedBox(height: 30),
            const SizedBox(
              width: 24,
              height: 24,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: Color(0xFFB50D11),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
