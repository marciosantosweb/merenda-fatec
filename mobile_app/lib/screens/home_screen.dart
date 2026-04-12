import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'package:intl/intl.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

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
          // Header Customizado
          Container(
            padding: const EdgeInsets.only(top: 60, bottom: 30, left: 20, right: 20),
            decoration: const BoxDecoration(
              color: Color(0xFF313131),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(30)),
              border: Border(bottom: BorderSide(color: Color(0xFFB50D11), width: 5)),
            ),
            child: const Center(
              child: Text(
                "MERENDA FATEC",
                style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold, letterSpacing: 1.5),
              ),
            ),
          ),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  // Card de Status
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(25),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05), blurRadius: 10, offset: const Offset(0, 5))],
                    ),
                    child: Column(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 5),
                          decoration: BoxDecoration(
                            color: _statusColor.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: _statusColor),
                          ),
                          child: Text(
                            _statusMessage,
                            style: TextStyle(color: _statusColor, fontWeight: FontWeight.bold, fontSize: 12),
                          ),
                        ),
                        const SizedBox(height: 15),
                        const Text("TEMPO RESTANTE", style: TextStyle(fontSize: 10, color: Colors.grey, fontWeight: FontWeight.bold)),
                        Text(
                          _timeRemaining,
                          style: TextStyle(fontSize: 36, fontWeight: FontWeight.bold, color: _statusColor),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 30),

                  // Seção de Cardápio (Apenas se não for fim de semana)
                  if (DateTime.now().weekday != DateTime.saturday && DateTime.now().weekday != DateTime.sunday)
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.restaurant, color: Color(0xFFB50D11), size: 20),
                            SizedBox(width: 10),
                            Text("CARDÁPIO DO DIA", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                          ],
                        ),
                        const SizedBox(height: 15),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(20),
                          decoration: const BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(15),
                            border: Border(left: BorderSide(color: Color(0xFFB50D11), width: 5)),
                          ),
                          child: const Text(
                            "Arroz, Feijão, Proteína e Salada",
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ),
                      ],
                    ),

                  const SizedBox(height: 40),

                  // Botão de Reserva
                  SizedBox(
                    width: double.infinity,
                    height: 60,
                    child: ElevatedButton(
                      onPressed: _canReserve ? () {} : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFB50D11),
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: Colors.grey[300],
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(50)),
                        elevation: 5,
                      ),
                      child: Text(
                        _canReserve ? "CONFIRMAR JANTA" : "FORA DO HORÁRIO",
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
