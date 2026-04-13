import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

/// Gerenciador do banco de dados local SQLite.
///
/// IMPORTANTE — Segurança de dados ao desinstalar:
/// O banco de dados é armazenado no diretório privado do app:
///   • Android: /data/data/<package>/databases/merenda_auth.db
///   • iOS:     <app_sandbox>/Documents/merenda_auth.db
///
/// Em ambas as plataformas, o Android e o iOS APAGAM automaticamente
/// toda a pasta privada do app ao desinstalar, incluindo este arquivo.
/// Não há necessidade de limpeza manual — nenhum dado de login persiste
/// após a desinstalação do Rango!
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
    // getDatabasesPath() retorna o diretório privado do app —
    // apagado automaticamente pelo sistema ao desinstalar.
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

  /// Salva sessão do usuário (substitui qualquer sessão anterior)
  Future<void> saveSession(Map<String, dynamic> session) async {
    final db = await database;
    await db.delete('user_session');
    await db.insert('user_session', session);
  }

  /// Retorna a sessão salva, ou null se não houver
  Future<Map<String, dynamic>?> getSession() async {
    final db = await database;
    final maps = await db.query('user_session');
    return maps.isNotEmpty ? maps.first : null;
  }

  /// Remove a sessão (logout manual ou expiração)
  Future<void> clearSession() async {
    final db = await database;
    await db.delete('user_session');
  }
}
