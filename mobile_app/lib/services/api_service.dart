import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // Mude para o seu domínio final para produção
  static const String baseUrl = 'https://www.etecsaosebastiao.com.br/fatec/merenda/api';
  // Use 'http://10.0.2.2/MERENDA/api' para emulador Android local

  // Buscar Cardápio do Dia
  Future<Map<String, dynamic>> getTodayMenu() async {
    try {
      final response = await http.get(Uri.parse('$baseUrl/v1/menu.php'));
      if (response.statusCode == 200) {
        return json.decode(response.body);
      }
      throw Exception('Erro ao carregar cardápio');
    } catch (e) {
      return {'error': e.toString()};
    }
  }

  // Realizar Reserva
  Future<Map<String, dynamic>> makeReservation(int userId, int repetitions) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/v1/reservation.php'),
        body: {
          'user_id': userId.toString(),
          'repetitions': repetitions.toString(),
        },
      );
      return json.decode(response.body);
    } catch (e) {
      return {'success': false, 'message': 'Erro de conexão'};
    }
  }

  // Buscar Configurações (Horários)
  Future<Map<String, dynamic>> getSettings() async {
    final response = await http.get(Uri.parse('$baseUrl/v1/settings.php'));
    return json.decode(response.body);
  }
}
