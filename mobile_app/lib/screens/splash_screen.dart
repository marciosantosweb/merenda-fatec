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

  // ── FASE 1: Slam (logo cai do topo com força) ────────────────────────────
  late AnimationController _slamController;
  late Animation<double> _slamScale;
  late Animation<double> _slamY;
  late Animation<double> _slamOpacity;

  // ── FASE 2: Impacto (shake da câmera + flash + ondas) ───────────────────
  late AnimationController _shakeController;
  late Animation<double> _shakeX;

  late AnimationController _flashController;
  late Animation<double> _flashOpacity;

  late AnimationController _wave1Controller;
  late AnimationController _wave2Controller;
  late AnimationController _wave3Controller;

  // ── FASE 3: Pulso vivo (logo respira) ───────────────────────────────────
  late AnimationController _pulseController;
  late Animation<double> _pulseScale;

  // ── FASE 4: Texto + subtítulo ────────────────────────────────────────────
  late AnimationController _textController;
  late Animation<double> _textScale;
  late Animation<double> _textOpacity;

  late AnimationController _subController;
  late Animation<double> _subOpacity;

  // ── FASE 5: Saída ────────────────────────────────────────────────────────
  late AnimationController _exitController;
  late Animation<double> _exitOpacity;
  late Animation<double> _exitScale;

  // ── Estado ───────────────────────────────────────────────────────────────
  bool _impactDone = false;

  @override
  void initState() {
    super.initState();
    _buildControllers();
    _runSequence();
  }

  void _buildControllers() {
    // SLAM: logo desce rápido do topo para o centro
    _slamController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 450),
    );
    _slamScale = TweenSequence([
      TweenSequenceItem(tween: Tween(begin: 2.5, end: 1.0).chain(CurveTween(curve: Curves.easeIn)), weight: 70),
      TweenSequenceItem(tween: Tween(begin: 1.0, end: 1.15).chain(CurveTween(curve: Curves.easeOut)), weight: 15),
      TweenSequenceItem(tween: Tween(begin: 1.15, end: 0.95).chain(CurveTween(curve: Curves.easeInOut)), weight: 10),
      TweenSequenceItem(tween: Tween(begin: 0.95, end: 1.0).chain(CurveTween(curve: Curves.easeOut)), weight: 5),
    ]).animate(_slamController);
    _slamY = Tween(begin: -120.0, end: 0.0).animate(
      CurvedAnimation(parent: _slamController, curve: const Interval(0.0, 0.7, curve: Curves.easeIn)),
    );
    _slamOpacity = Tween(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _slamController, curve: const Interval(0.0, 0.2, curve: Curves.easeIn)),
    );

    // SHAKE: câmera treme no impacto
    _shakeController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 380),
    );
    _shakeX = TweenSequence([
      TweenSequenceItem(tween: Tween(begin: 0.0, end: 14.0), weight: 10),
      TweenSequenceItem(tween: Tween(begin: 14.0, end: -12.0), weight: 15),
      TweenSequenceItem(tween: Tween(begin: -12.0, end: 9.0), weight: 15),
      TweenSequenceItem(tween: Tween(begin: 9.0, end: -6.0), weight: 20),
      TweenSequenceItem(tween: Tween(begin: -6.0, end: 4.0), weight: 20),
      TweenSequenceItem(tween: Tween(begin: 4.0, end: -2.0), weight: 10),
      TweenSequenceItem(tween: Tween(begin: -2.0, end: 0.0), weight: 10),
    ]).animate(CurvedAnimation(parent: _shakeController, curve: Curves.linear));

    // FLASH: clarão branco no impacto
    _flashController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );
    _flashOpacity = TweenSequence([
      TweenSequenceItem(tween: Tween(begin: 0.0, end: 0.85), weight: 30),
      TweenSequenceItem(tween: Tween(begin: 0.85, end: 0.0), weight: 70),
    ]).animate(_flashController);

    // ONDAS DE CHOQUE
    _wave1Controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 600));
    _wave2Controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 700));
    _wave3Controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 800));

    // PULSO: logo fica "viva" com respiro suave
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);
    _pulseScale = Tween(begin: 1.0, end: 1.04).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );

    // TEXTO: zoom pop
    _textController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _textScale = Tween(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _textController, curve: Curves.elasticOut),
    );
    _textOpacity = Tween(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _textController, curve: const Interval(0.0, 0.3, curve: Curves.easeIn)),
    );

    // SUBTÍTULO
    _subController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 350),
    );
    _subOpacity = Tween(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _subController, curve: Curves.easeIn),
    );

    // SAÍDA: zoom out + fade
    _exitController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 550),
    );
    _exitOpacity = Tween(begin: 1.0, end: 0.0).animate(
      CurvedAnimation(parent: _exitController, curve: Curves.easeIn),
    );
    _exitScale = Tween(begin: 1.0, end: 1.15).animate(
      CurvedAnimation(parent: _exitController, curve: Curves.easeIn),
    );
  }

  Future<void> _runSequence() async {
    await Future.delayed(const Duration(milliseconds: 150));

    // 1. SLAM
    await _slamController.forward();

    // 2. IMPACTO simultâneo
    setState(() => _impactDone = true);
    _shakeController.forward();
    _flashController.forward();
    _wave1Controller.forward();
    await Future.delayed(const Duration(milliseconds: 80));
    _wave2Controller.forward();
    await Future.delayed(const Duration(milliseconds: 80));
    _wave3Controller.forward();

    await Future.delayed(const Duration(milliseconds: 450));

    // 3. Texto pop
    await _textController.forward();
    await Future.delayed(const Duration(milliseconds: 80));
    await _subController.forward();

    // 4. Respira por um momento
    await Future.delayed(const Duration(milliseconds: 950));

    // 5. Saída + carrega sessão
    _pulseController.stop();
    final sessionFuture = widget.onComplete();
    await _exitController.forward();
    await sessionFuture;
  }

  @override
  void dispose() {
    _slamController.dispose();
    _shakeController.dispose();
    _flashController.dispose();
    _wave1Controller.dispose();
    _wave2Controller.dispose();
    _wave3Controller.dispose();
    _pulseController.dispose();
    _textController.dispose();
    _subController.dispose();
    _exitController.dispose();
    super.dispose();
  }

  Widget _buildWave(AnimationController ctrl, Color color, double baseSize) {
    return AnimatedBuilder(
      animation: ctrl,
      builder: (_, __) {
        final scale = Tween(begin: 0.5, end: 2.2).evaluate(
          CurvedAnimation(parent: ctrl, curve: Curves.easeOut),
        );
        final opacity = Tween(begin: 0.7, end: 0.0).evaluate(
          CurvedAnimation(parent: ctrl, curve: Curves.easeOut),
        );
        return Transform.scale(
          scale: scale,
          child: Opacity(
            opacity: opacity,
            child: Container(
              width: baseSize,
              height: baseSize,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: color, width: 2.0),
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _exitController,
      builder: (_, child) => Opacity(
        opacity: _exitOpacity.value,
        child: Transform.scale(scale: _exitScale.value, child: child),
      ),
      child: Scaffold(
        backgroundColor: Colors.black,
        body: Stack(
          fit: StackFit.expand,
          children: [
            // ── Partículas de fundo ──────────────────────────────────────
            const _ParticleBackground(),

            // ── Conteúdo principal (com shake) ───────────────────────────
            AnimatedBuilder(
              animation: _shakeController,
              builder: (_, child) => Transform.translate(
                offset: Offset(_shakeX.value, 0),
                child: child,
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Stack: ondas + logo
                  SizedBox(
                    width: 220,
                    height: 220,
                    child: Stack(
                      alignment: Alignment.center,
                      children: [
                        if (_impactDone) ...[
                          _buildWave(_wave1Controller, const Color(0xFFB50D11), 160),
                          _buildWave(_wave2Controller, Colors.white24, 160),
                          _buildWave(_wave3Controller, const Color(0xFFB50D11), 160),
                        ],

                        // Logo com slam + pulse
                        AnimatedBuilder(
                          animation: Listenable.merge([_slamController, _pulseController]),
                          builder: (_, child) => Transform.translate(
                            offset: Offset(0, _slamY.value),
                            child: Transform.scale(
                              scale: _slamScale.value * _pulseScale.value,
                              child: Opacity(opacity: _slamOpacity.value, child: child),
                            ),
                          ),
                          child: Image.asset(
                            'assets/images/logo-splash.png',
                            width: 160,
                            fit: BoxFit.contain,
                            errorBuilder: (_, __, ___) => const Icon(
                              Icons.restaurant_menu,
                              color: Colors.white,
                              size: 130,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Texto "Rango!" — zoom pop
                  AnimatedBuilder(
                    animation: _textController,
                    builder: (_, child) => Transform.scale(
                      scale: _textScale.value,
                      child: Opacity(opacity: _textOpacity.value, child: child),
                    ),
                    child: const Text(
                      'Rango!',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 44,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 3.0,
                        height: 1,
                      ),
                    ),
                  ),

                  const SizedBox(height: 10),

                  AnimatedBuilder(
                    animation: _subController,
                    builder: (_, child) => Opacity(opacity: _subOpacity.value, child: child),
                    child: const Text(
                      'FATEC SÃO SEBASTIÃO',
                      style: TextStyle(
                        color: Color(0xFF666666),
                        fontSize: 11,
                        letterSpacing: 4.0,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ── Flash branco no impacto ──────────────────────────────────
            AnimatedBuilder(
              animation: _flashController,
              builder: (_, __) => IgnorePointer(
                child: Opacity(
                  opacity: _flashOpacity.value,
                  child: Container(color: Colors.white),
                ),
              ),
            ),

            // ── Linha vermelha inferior ──────────────────────────────────
            Positioned(
              bottom: 0, left: 0, right: 0,
              child: AnimatedBuilder(
                animation: _slamController,
                builder: (_, __) => Opacity(
                  opacity: _slamOpacity.value,
                  child: Container(height: 4, color: const Color(0xFFB50D11)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Partículas flutuantes ─────────────────────────────────────────────────────
class _ParticleBackground extends StatefulWidget {
  const _ParticleBackground();
  @override
  State<_ParticleBackground> createState() => _ParticleBackgroundState();
}

class _ParticleBackgroundState extends State<_ParticleBackground>
    with SingleTickerProviderStateMixin {
  late AnimationController _ctrl;
  final _rng = Random();
  late final List<_Particle> _particles;

  @override
  void initState() {
    super.initState();
    _particles = List.generate(40, (_) => _Particle(
      x: _rng.nextDouble(),
      y: _rng.nextDouble(),
      size: _rng.nextDouble() * 2.0 + 0.5,
      opacity: _rng.nextDouble() * 0.20 + 0.04,
      speed: _rng.nextDouble() * 0.00025 + 0.00008,
    ));
    _ctrl = AnimationController(vsync: this, duration: const Duration(seconds: 12))..repeat();
    _ctrl.addListener(() { if (mounted) setState(() {}); });
  }

  @override
  void dispose() { _ctrl.dispose(); super.dispose(); }

  @override
  Widget build(BuildContext context) => CustomPaint(
    size: MediaQuery.of(context).size,
    painter: _ParticlePainter(_particles, _ctrl.value),
  );
}

class _Particle {
  final double x, y, size, opacity, speed;
  _Particle({required this.x, required this.y, required this.size,
             required this.opacity, required this.speed});
}

class _ParticlePainter extends CustomPainter {
  final List<_Particle> particles;
  final double progress;
  _ParticlePainter(this.particles, this.progress);

  @override
  void paint(Canvas canvas, Size size) {
    for (final p in particles) {
      final dy = (p.y - progress * p.speed * 100) % 1.0;
      canvas.drawCircle(
        Offset(p.x * size.width, dy * size.height),
        p.size,
        Paint()..color = Colors.white.withOpacity(p.opacity),
      );
    }
  }

  @override
  bool shouldRepaint(_ParticlePainter old) => true;
}
