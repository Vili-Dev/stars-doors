<?php
/**
 * Script de compilation SASS pour Stars Doors
 * Utilise la ligne de commande SASS pour compiler les fichiers
 */

// Configuration
$scss_dir = __DIR__ . '/assets/scss/';
$css_dir = __DIR__ . '/assets/css/';
$main_scss = $scss_dir . 'main.scss';
$output_css = $css_dir . 'style.css';

// Vérifier que le dossier CSS existe
if (!is_dir($css_dir)) {
    mkdir($css_dir, 0755, true);
    echo "📁 Dossier CSS créé\n";
}

// Fonction pour exécuter SASS
function compileSass($input, $output, $style = 'expanded', $sourcemap = true) {
    $sourcemap_flag = $sourcemap ? '--source-map' : '--no-source-map';
    $command = "sass \"$input\" \"$output\" --style $style $sourcemap_flag";
    
    echo "🔄 Compilation SASS...\n";
    echo "📁 Source: $input\n";
    echo "📁 Destination: $output\n";
    echo "⚙️ Commande: $command\n\n";
    
    exec($command, $output_lines, $return_code);
    
    return [
        'success' => $return_code === 0,
        'output' => $output_lines,
        'command' => $command
    ];
}

// Vérifier que SASS est installé
exec('sass --version', $version_output, $sass_available);
if ($sass_available !== 0) {
    echo "❌ SASS n'est pas installé ou n'est pas dans le PATH\n";
    echo "💡 Pour installer SASS :\n";
    echo "   - Via npm: npm install -g sass\n";
    echo "   - Via chocolatey (Windows): choco install sass\n";
    echo "   - Via brew (macOS): brew install sass/sass/sass\n";
    echo "   - Télécharger depuis: https://sass-lang.com/install\n";
    exit(1);
}

echo "✅ SASS détecté: " . implode(' ', $version_output) . "\n\n";

// Vérifier que le fichier SCSS principal existe
if (!file_exists($main_scss)) {
    echo "❌ Fichier SCSS principal non trouvé: $main_scss\n";
    exit(1);
}

// Déterminer le mode de compilation
$mode = $argv[1] ?? 'dev';
$watch = isset($argv[2]) && $argv[2] === '--watch';

echo "🎯 Mode: $mode\n";
if ($watch) {
    echo "👀 Mode watch activé\n";
}
echo "\n";

// Configuration selon le mode
switch ($mode) {
    case 'prod':
    case 'production':
        $style = 'compressed';
        $sourcemap = false;
        echo "🚀 Compilation pour la PRODUCTION\n";
        break;
    
    case 'dev':
    case 'development':
    default:
        $style = 'expanded';
        $sourcemap = true;
        echo "🛠️ Compilation pour le DÉVELOPPEMENT\n";
        break;
}

// Compiler
if ($watch) {
    // Mode watch
    $sourcemap_flag = $sourcemap ? '--source-map' : '--no-source-map';
    $command = "sass \"$main_scss\" \"$output_css\" --style $style $sourcemap_flag --watch";
    
    echo "👀 Surveillance des fichiers SCSS activée...\n";
    echo "🔄 Appuyez sur Ctrl+C pour arrêter\n\n";
    
    // Exécution en mode watch (bloquant)
    passthru($command);
} else {
    // Compilation unique
    $result = compileSass($main_scss, $output_css, $style, $sourcemap);
    
    if ($result['success']) {
        echo "✅ Compilation réussie !\n";
        
        // Afficher les informations du fichier généré
        if (file_exists($output_css)) {
            $file_size = filesize($output_css);
            $formatted_size = formatBytes($file_size);
            echo "📄 Fichier généré: $output_css\n";
            echo "📏 Taille: $formatted_size\n";
            
            // Compter les lignes de CSS
            $lines = count(file($output_css));
            echo "📝 Lignes de CSS: $lines\n";
        }
        
        // Afficher les warnings/infos de SASS si présents
        if (!empty($result['output'])) {
            echo "\n📋 Sortie SASS :\n";
            foreach ($result['output'] as $line) {
                echo "   $line\n";
            }
        }
        
    } else {
        echo "❌ Erreur de compilation SASS\n\n";
        echo "📋 Détails de l'erreur :\n";
        foreach ($result['output'] as $line) {
            echo "   $line\n";
        }
        echo "\n💡 Vérifiez la syntaxe de vos fichiers SCSS\n";
        exit(1);
    }
}

// Fonction utilitaire pour formater les tailles de fichiers
function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

echo "\n🎉 Terminé !\n";
?>