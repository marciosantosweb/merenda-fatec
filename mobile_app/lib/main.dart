import 'package:flutter/material.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const MerendaFatecApp());
}

class MerendaFatecApp extends StatelessWidget {
  const MerendaFatecApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Merenda Fatec',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        // Identidade Visual Fatec
        primaryColor: const Color(0xFF8B0000), // Primary Red
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFB50D11),
          primary: const Color(0xFFB50D11),
          secondary: const Color(0xFF313131), // Dark Gray
        ),
        fontFamily: 'Raleway',
        useMaterial3: true,
      ),
      home: HomeScreen(),
    );
  }
}
