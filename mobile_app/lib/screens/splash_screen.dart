import 'dart:math';
import 'package:flutter/material.dart';

class SplashScreen extends StatefulWidget {
  final Future<void> Function() onComplete;
  const SplashScreen({super.key, required this.onComplete});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  // ── Animation Controllers ─────────────────────────────────────────────────
  late AnimationController _logoController;
  late AnimationController _ringController;
  late AnimationController _textController;
  late AnimationController _subtitleController;
  late AnimationController _exitController;

  // ── Animations ────────────────────────────────────────────────────────────
  late Animation<double> _logoScale;
  late Animation<double> _logoOpacity;
  late Animation<double> _ringScale;
  late Animation<double> _ringOpacity;
  late Animation<double> _textOpacity;
  late Animation<Offset> _textSlide;
  late Animation<double> _subtitleOpacity;
  late Animation<double> _exitOpacity;

  @override
  void initState() {
    super.initState();
    _setupAnimations();
    _runSequence();
  }

  void _setupAnimations() {
    // Logo: scale elástico + fade in
    _logoController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _logoScale = Tween<double>(begin: 0.3, end: 1.0).animate(
      CurvedAnimation(parent: _logoController, curve: Curves.elasticOut),
    );
    _logoOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _logoController,
        curve: const Interval(0.0, 0.4, curve: Curves.easeIn),
      ),
    );

    // Anel pulsante vermelho
    _ringController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _ringScale = Tween<double>(begin: 0.6, end: 1.5).animate(
      CurvedAnimation(parent: _ringController, curve: Curves.easeOut),
    );
    _ringOpacity = Tween<double>(begin: 0.8, end: 0.0).animate(
      CurvedAnimation(parent: _ringController, curve: Curves.easeOut),
    );

    // Texto "Rango!": slide up + fade in
    _textController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 550),
    );
    _textOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _textController, curve: Curves.easeOut),
    );
    _textSlide = Tween<Offset>(
      begin: const Offset(0, 0.5),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(parent: _textController, curve: Curves.easeOut),
    );

    // Subtítulo: fade in
    _subtitleController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _subtitleOpacity = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _subtitleController, curve: Curves.easeIn),
    );

    // Exit: fade out de tudo
    _exitController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 500),
    );
    _exitOpacity = Tween<double>(begin: 1.0, end: 0.0).animate(
      CurvedAnimation(parent: _exitController, curve: Curves.easeIn),
    );
  }

  Future<void> _runSequence() async {
    await Future.delayed(const Duration(milliseconds: 200));

    // 1. Logo entra com escala elástica
    await _logoController.forward();

    // 2. Anel pulsa (simultâneo ao fim da logo)
    _ringController.forward();
    await Future.delayed(const Duration(milliseconds: 300));

    // 3. Texto "Rango!" sobe e aparece
    await _textController.forward();
    await Future.delayed(const Duration(milliseconds: 100));

    // 4. Subtítulo aparece suavemente
    await _subtitleController.forward();

    // 5. Aguarda um momento para admirar rs
    await Future.delayed(const Duration(milliseconds: 900));

    // 6. Carrega a sessão em paralelo com o fade-out
    final sessionFuture = widget.onComplete();
    await _exitController.forward();
    await sessionFuture;
  }

  @override
  void dispose() {
    _logoController.dispose();
    _ringController.dispose();
    _textController.dispose();
    _subtitleController.dispose();
    _exitController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _exitController,
      builder: (context, child) {
        return Opacity(
          opacity: _exitOpacity.value,
          child: child,
        );
      },
      child: Scaffold(
        backgroundColor: Colors.black,
        body: Stack(
          alignment: Alignment.center,
          children: [
            // ── Partículas de fundo ──────────────────────────────────────────
            const _ParticleBackground(),

            // ── Conteúdo Central ─────────────────────────────────────────────
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Anel pulsante ao redor do logo
                Stack(
                  alignment: Alignment.center,
                  children: [
                    // Anel vermelho que expande e some
                    AnimatedBuilder(
                      animation: _ringController,
                      builder: (context, _) {
                        return Transform.scale(
                          scale: _ringScale.value,
                          child: Opacity(
                            opacity: _ringOpacity.value,
                            child: Container(
                              width: 140,
                              height: 140,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: const Color(0xFFB50D11),
                                  width: 2.5,
                                ),
                              ),
                            ),
                          ),
                        );
                      },
                    ),

                    // Logo principal
                    AnimatedBuilder(
                      animation: _logoController,
                      builder: (context, child) {
                        return Transform.scale(
                          scale: _logoScale.value,
                          child: Opacity(
                            opacity: _logoOpacity.value,
                            child: child,
                          ),
                        );
                      },
                      child: Image.asset(
                        'assets/images/logo-rango.png',
                        height: 100,
                        color: Colors.white,
                        colorBlendMode: BlendMode.srcIn,
                        errorBuilder: (_, __, ___) => const Icon(
                          Icons.restaurant_menu,
                          color: Colors.white,
                          size: 90,
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 28),

                // Texto "Rango!"
                AnimatedBuilder(
                  animation: _textController,
                  builder: (context, child) {
                    return SlideTransition(
                      position: _textSlide,
                      child: Opacity(
                        opacity: _textOpacity.value,
                        child: child,
                      ),
                    );
                  },
                  child: const Text(
                    'Rango!',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 42,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 2.0,
                      height: 1,
                    ),
                  ),
                ),

                const SizedBox(height: 10),

                // Subtítulo
                AnimatedBuilder(
                  animation: _subtitleController,
                  builder: (context, child) {
                    return Opacity(
                      opacity: _subtitleOpacity.value,
                      child: child,
                    );
                  },
                  child: const Text(
                    'Fatec São Sebastião',
                    style: TextStyle(
                      color: Color(0xFF888888),
                      fontSize: 13,
                      letterSpacing: 3.0,
                      fontWeight: FontWeight.w400,
                    ),
                  ),
                ),
              ],
            ),

            // ── Linha decorativa vermelha inferior ───────────────────────────
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: AnimatedBuilder(
                animation: _logoController,
                builder: (context, _) {
                  return Opacity(
                    opacity: _logoOpacity.value,
                    child: Container(height: 4, color: const Color(0xFFB50D11)),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Widget de Partículas Sutis ────────────────────────────────────────────────
class _ParticleBackground extends StatefulWidget {
  const _ParticleBackground();

  @override
  State<_ParticleBackground> createState() => _ParticleBackgroundState();
}

class _ParticleBackgroundState extends State<_ParticleBackground>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  final List<_Particle> _particles = [];
  final Random _rng = Random();

  @override
  void initState() {
    super.initState();
    for (int i = 0; i < 35; i++) {
      _particles.add(_Particle(
        x: _rng.nextDouble(),
        y: _rng.nextDouble(),
        size: _rng.nextDouble() * 2.5 + 0.5,
        opacity: _rng.nextDouble() * 0.25 + 0.05,
        speed: _rng.nextDouble() * 0.0003 + 0.0001,
      ));
    }
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 10),
    )..repeat();
    _controller.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    return CustomPaint(
      size: size,
      painter: _ParticlePainter(_particles, _controller.value),
    );
  }
}

class _Particle {
  final double x, y, size, opacity, speed;
  _Particle({
    required this.x,
    required this.y,
    required this.size,
    required this.opacity,
    required this.speed,
  });
}

class _ParticlePainter extends CustomPainter {
  final List<_Particle> particles;
  final double progress;
  _ParticlePainter(this.particles, this.progress);

  @override
  void paint(Canvas canvas, Size size) {
    for (final p in particles) {
      final dy = (p.y + progress * p.speed * 100) % 1.0;
      final paint = Paint()
        ..color = Colors.white.withOpacity(p.opacity)
        ..style = PaintingStyle.fill;
      canvas.drawCircle(
        Offset(p.x * size.width, dy * size.height),
        p.size,
        paint,
      );
    }
  }

  @override
  bool shouldRepaint(_ParticlePainter old) => true;
}
