import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class DatabaseHelper {
  static final DatabaseHelper _instance = DatabaseHelper._internal();
  factory DatabaseHelper() => _instance;
  DatabaseHelper._internal();

  static Database? _database;

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async {
    String path = join(await getDatabasesPath(), 'merenda_auth.db');
    return await openDatabase(
      path,
      version: 1,
      onCreate: _onCreate,
    );
  }

  Future _onCreate(Database db, int version) async {
    await db.execute('''
      CREATE TABLE user_session (
        id INTEGER PRIMARY KEY,
        name TEXT,
        email TEXT,
        microsoft_token TEXT,
        role TEXT,
        login_date TEXT,
        expires_at TEXT
      )
    ''');
  }

  // Salvar sessão do usuário
  Future<void> saveSession(Map<String, dynamic> session) async {
    final db = await database;
    await db.delete('user_session'); // Limpa sessões anteriores
    await db.insert('user_session', session);
  }

  // Buscar sessão ativa
  Future<Map<String, dynamic>?> getSession() async {
    final db = await database;
    List<Map<String, dynamic>> maps = await db.query('user_session');
    if (maps.isNotEmpty) {
      return maps.first;
    }
    return null;
  }

  // Logout
  Future<void> clearSession() async {
    final db = await database;
    await db.delete('user_session');
  }
}
