import 'dart:async';
import 'package:flutter/material.dart';
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
  String _timeRemaining = "--:--:--";
  String _statusMessage = "Aguarde...";
  Color _statusColor = Colors.grey;
  bool _canReserve = false;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  void _startTimer() {
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _updateStatus();
    });
  }

  void _updateStatus() {
    final now = DateTime.now();
    // Exemplo de horários (Na vida real viria da API)
    final startTime = DateTime(now.year, now.month, now.day, 18, 0);
    final endTime = DateTime(now.year, now.month, now.day, 19, 30);

    setState(() {
      if (now.weekday == DateTime.saturday || now.weekday == DateTime.sunday) {
        _statusMessage = "FIM DE SEMANA";
        _statusColor = Colors.blueGrey;
        _canReserve = false;
        _timeRemaining = "--:--";
      } else if (now.isBefore(startTime)) {
        _statusMessage = "AGUARDANDO ABERTURA";
        _statusColor = Colors.orange;
        _canReserve = false;
        _timeRemaining = "Abre às 18:00";
      } else if (now.isAfter(endTime)) {
        _statusMessage = "JANELA ENCERRADA";
        _statusColor = Colors.red;
        _canReserve = false;
        _timeRemaining = "Até amanhã!";
      } else {
        _statusMessage = "JANELA ABERTA";
        _statusColor = Colors.green;
        _canReserve = true;
        
        final diff = endTime.difference(now);
        _timeRemaining = _printDuration(diff);
      }
    });
  }

  String _printDuration(Duration duration) {
    String twoDigits(int n) => n.toString().padLeft(2, "0");
    String twoDigitMinutes = twoDigits(duration.inMinutes.remainder(60));
    String twoDigitSeconds = twoDigits(duration.inSeconds.remainder(60));
    return "${twoDigits(duration.inHours)}:$twoDigitMinutes:$twoDigitSeconds";
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
                          child: const Row(
                            children: [
                              Expanded(
                                child: Text(
                                  "Arroz, Feijão, Proteína e Salada",
                                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16, color: Color(0xFF4A4A4A)),
                                ),
                              ),
                              Icon(Icons.arrow_forward_ios, size: 14, color: Colors.grey),
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
                      onPressed: _canReserve ? () {} : null,
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
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
