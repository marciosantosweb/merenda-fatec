import 'dart:async';
import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'package:intl/intl.dart';

class HomeScreen extends StatefulWidget {
  final Map<String, dynamic> user;
  const HomeScreen({super.key, required this.user});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ApiService _apiService = ApiService();
  String _timeRemaining = "--:--:--";
  String _statusMessage = "Carregando...";
  Color _statusColor = Colors.grey;
  bool _canReserve = false;
  Timer? _timer;
  String _appVersion = '';

  bool _isLoadingData = true;
  String _menuDescription = "Buscando cardápio...";
  DateTime? _startTime;
  DateTime? _endTime;
  bool _hasReservation = false;

  @override
  void initState() {
    super.initState();
    _loadVersion();
    _loadApiData();
    _startTimer();
  }

  Future<void> _loadVersion() async {
    final info = await PackageInfo.fromPlatform();
    if (mounted) setState(() => _appVersion = info.version);
  }

  Future<void> _loadApiData() async {
    setState(() { _isLoadingData = true; });

    final menuRes = await _apiService.getTodayMenu();
    if (menuRes['status'] == 'success') {
      _menuDescription = menuRes['menu'] ?? 'Cardápio não informado';
    } else {
      _menuDescription = menuRes['message'] ?? 'Hoje não haverá merenda.';
    }

    final settingsRes = await _apiService.getSettings();
    if (settingsRes['status'] == 'success' && settingsRes['data'] != null) {
      final window = settingsRes['data']['reservation_window'];
      final now = DateTime.now();
      try {
        final startParts = window['start'].split(':');
        final endParts = window['end'].split(':');
        _startTime = DateTime(now.year, now.month, now.day, int.parse(startParts[0]), int.parse(startParts[1]));
        _endTime = DateTime(now.year, now.month, now.day, int.parse(endParts[0]), int.parse(endParts[1]));
      } catch (e) {
        // ignore fallback
      }
    }

    final resStatus = await _apiService.getReservationStatus(widget.user['id']);
    if (resStatus['success'] == true) {
      _hasReservation = resStatus['has_reservation'] == true;
    }

    if (mounted) {
      setState(() { _isLoadingData = false; });
      _updateStatus();
    }
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _updateStatus();
    });
  }

  void _updateStatus() {
    if (_isLoadingData) return;

    final now = DateTime.now();
    final start = _startTime ?? DateTime(now.year, now.month, now.day, 18, 0);
    final end = _endTime ?? DateTime(now.year, now.month, now.day, 19, 30);

    setState(() {
      if (now.weekday == DateTime.saturday || now.weekday == DateTime.sunday) {
        _statusMessage = "FIM DE SEMANA";
        _statusColor = Colors.blueGrey;
        _canReserve = false;
        _timeRemaining = "--:--";
      } else if (_hasReservation) {
        _statusMessage = "JANTA CONFIRMADA";
        _statusColor = Colors.green[700]!;
        _canReserve = false;
        _timeRemaining = "Bom apetite!";
      } else if (now.isBefore(start)) {
        _statusMessage = "AGUARDANDO ABERTURA";
        _statusColor = Colors.orange;
        _canReserve = false;
        _timeRemaining = "Abre às ${start.hour.toString().padLeft(2, '0')}:${start.minute.toString().padLeft(2, '0')}";
      } else if (now.isAfter(end)) {
        _statusMessage = "JANELA ENCERRADA";
        _statusColor = Colors.red;
        _canReserve = false;
        _timeRemaining = "Até amanhã!";
      } else {
        _statusMessage = "JANELA ABERTA";
        _statusColor = Colors.green;
        _canReserve = true;
        
        final diff = end.difference(now);
        _timeRemaining = _printDuration(diff);
      }
    });
  }

  String _printDuration(Duration duration) {
    if (duration.isNegative) return "00:00:00";
    String twoDigits(int n) => n.toString().padLeft(2, "0");
    String twoDigitMinutes = twoDigits(duration.inMinutes.remainder(60));
    String twoDigitSeconds = twoDigits(duration.inSeconds.remainder(60));
    return "${twoDigits(duration.inHours)}:$twoDigitMinutes:$twoDigitSeconds";
  }

  Future<void> _makeReservation() async {
    setState(() {
      _canReserve = false;
      _statusMessage = "Processando...";
    });
    final res = await _apiService.makeReservation(widget.user['id'], 1);
    if (!mounted) return;
    if (res['success'] == true) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Reserva confirmada!'), backgroundColor: Colors.green));
      _loadApiData();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Erro ao confirmar de reserva.'), backgroundColor: Colors.red));
      setState(() { _canReserve = true; });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      body: Column(
        children: [
          // Header Customizado com Logo Fatec + Nome e Logout
          Container(
            padding: const EdgeInsets.only(top: 55, bottom: 18, left: 20, right: 12),
            decoration: const BoxDecoration(
              color: Color(0xFF313131),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(30)),
              border: Border(bottom: BorderSide(color: Color(0xFFB50D11), width: 5)),
            ),
            child: Row(
              children: [
                // Logo Fatec
                Image.asset(
                  'assets/images/logo-fatec.png',
                  height: 36,
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) =>
                      const Icon(Icons.school, color: Colors.white, size: 36),
                ),
                const Spacer(),
                // Nome do usuário
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      'Olá, ${widget.user['name']?.toString().split(' ').first ?? 'Aluno'}!',
                      style: const TextStyle(
                        color: Colors.white70,
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Text(
                      widget.user['role'] == 'admin' ? 'Administrador' : 'Aluno',
                      style: const TextStyle(color: Colors.white38, fontSize: 10),
                    ),
                  ],
                ),
                const SizedBox(width: 4),
                // Botão Logout
                IconButton(
                  icon: const Icon(Icons.logout, color: Colors.white38, size: 20),
                  onPressed: () async {
                    await AuthService().signOut();
                    if (!context.mounted) return;
                    Navigator.of(context).pushReplacementNamed('/login');
                  },
                ),
              ],
            ),
          ),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                children: [
                  const SizedBox(height: 20),
                  
                  // Logo Rango (Vazado)
                  Image.asset(
                    'assets/images/logo-rango.png',
                    height: 100,
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) => const Icon(Icons.restaurant_menu, color: Color(0xFFB50D11), size: 60),
                  ),

                  const SizedBox(height: 20),

                  // Card de Status
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(25),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 10,
                          offset: const Offset(0, 5),
                        )
                      ],
                    ),
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 8),
                          decoration: BoxDecoration(
                            color: _statusColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: _statusColor, width: 2),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(_canReserve ? Icons.check_circle : Icons.info_outline, color: _statusColor, size: 16),
                              const SizedBox(width: 8),
                              Text(
                                _statusMessage,
                                style: TextStyle(color: _statusColor, fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.access_time_filled, size: 14, color: Colors.grey[400]),
                            const SizedBox(width: 5),
                            const Text(
                              "TEMPO RESTANTE",
                              style: TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.bold, letterSpacing: 1),
                            ),
                          ],
                        ),
                        const SizedBox(height: 5),
                        Text(
                          _timeRemaining,
                          style: TextStyle(
                            fontSize: 42,
                            fontWeight: FontWeight.bold,
                            color: _statusColor,
                            letterSpacing: -1,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 30),

                  // Seção de Cardápio
                  if (DateTime.now().weekday != DateTime.saturday && DateTime.now().weekday != DateTime.sunday)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Stack(
                              alignment: Alignment.center,
                              children: [
                                Icon(Icons.circle, color: Color(0xFFB50D11), size: 32),
                                Icon(Icons.restaurant, color: Colors.white, size: 18),
                              ],
                            ),
                            SizedBox(width: 12),
                            Text(
                              "CARDÁPIO DO DIA",
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF313131)),
                            ),
                          ],
                        ),
                        const SizedBox(height: 15),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(15),
                            border: const Border(left: BorderSide(color: Color(0xFFB50D11), width: 6)),
                            boxShadow: [
                              BoxShadow(color: Colors.black.withOpacity(0.02), blurRadius: 5, offset: const Offset(2, 2))
                            ],
                          ),
                          child: Row(
                            children: [
                              Expanded(
                                child: _isLoadingData ? const LinearProgressIndicator(color: Color(0xFFB50D11)) : Text(
                                  _menuDescription,
                                  style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16, color: Color(0xFF4A4A4A)),
                                ),
                              ),
                              const Icon(Icons.arrow_forward_ios, size: 14, color: Colors.grey),
                            ],
                          ),
                        ),
                      ],
                    ),

                  const SizedBox(height: 40),

                  // Botão de Reserva
                  Container(
                    width: double.infinity,
                    height: 65,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(50),
                      boxShadow: _canReserve 
                        ? [BoxShadow(color: const Color(0xFFB50D11).withOpacity(0.3), blurRadius: 15, offset: const Offset(0, 8))]
                        : [],
                    ),
                    child: ElevatedButton(
                      onPressed: _canReserve ? _makeReservation : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFB50D11),
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: Colors.grey[300],
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(50)),
                        elevation: 0,
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          if (_canReserve) const Icon(Icons.flatware),
                          if (_canReserve) const SizedBox(width: 10),
                          Text(
                            _canReserve ? "CONFIRMAR JANTA" : "FORA DO HORÁRIO",
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 30),

                  // ── Rodapé ─────────────────────────────────────
                  Text(
                    _appVersion.isNotEmpty ? 'v$_appVersion' : '',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 10,
                      color: Color(0xFFCCCCCC),
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 2),
                  const Text(
                    'Desenvolvido por NTI Etec São Sebastião\nProf. Marcio Santos',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 10,
                      color: Color(0xFFCCCCCC),
                      height: 1.6,
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
