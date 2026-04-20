import os
import sys

# Find the app-level build.gradle or build.gradle.kts
for root, dirs, files in os.walk('android'):
    for fname in files:
        if fname in ('build.gradle.kts', 'build.gradle') and os.path.basename(root) == 'app':
            path = os.path.join(root, fname)
            print(f"Found: {path}")
            with open(path, 'r') as f:
                content = f.read()

            print("--- CURRENT CONTENT ---")
            print(content)
            print("--- END CONTENT ---")

            is_kts = path.endswith('.kts')
            flag = 'isCoreLibraryDesugaringEnabled = true' if is_kts else 'coreLibraryDesugaringEnabled true'
            dep = 'coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.0.4")' if is_kts else "coreLibraryDesugaring 'com.android.tools:desugar_jdk_libs:2.0.4'"

            # Add desugaring flag inside compileOptions
            if flag not in content:
                if 'compileOptions {' in content:
                    content = content.replace('compileOptions {', 'compileOptions {\n        ' + flag, 1)
                    print(f"Added flag: {flag}")
                else:
                    print("WARNING: compileOptions block not found!")

            # Add desugaring dependency
            if dep not in content:
                if 'dependencies {' in content:
                    content = content.replace('dependencies {', 'dependencies {\n    ' + dep, 1)
                    print(f"Added dep inside existing dependencies block")
                else:
                    # No dependencies block exists - append one at the end
                    content += f'\ndependencies {{\n    {dep}\n}}\n'
                    print(f"Created new dependencies block with dep")

            with open(path, 'w') as f:
                f.write(content)

            print(f"Successfully patched {path}!")
            sys.exit(0)

print("ERROR: app/build.gradle(.kts) not found!")
sys.exit(1)
