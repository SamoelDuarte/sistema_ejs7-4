<?php
// create_migration.php

// Define o nome do arquivo de migração baseado na data e hora atual
$date = new DateTime();
// $nomeMigrate = "Remove_desconto_frete_from_ga845_configuracao";//exemplo de remover coluna
$nomeMigrate = "Migration_create_table_devices";
$timestamp = $date->format('YmdHis');
$migrationName = $timestamp . "_".$nomeMigrate.".php"; // Substitua Nome_da_migracao

// Caminho para o diretório de migrações
$migrationPath = __DIR__ . '/application/migrations/';

// Verifica se o diretório de migrações existe, se não, cria o diretório
if (!is_dir($migrationPath)) {
    mkdir($migrationPath, 0777, true);
}

// Conteúdo básico do arquivo de migração
$content = "<?php\n"
         . "class Migration_".$nomeMigrate." extends CI_Migration {\n"
         . "    public function up() {\n"
         . "        // Código de migração UP\n"
         . "    }\n\n"
         . "    public function down() {\n"
         . "        // Código de migração DOWN\n"
         . "    }\n"
         . "}\n";

// Cria o arquivo de migração
file_put_contents($migrationPath . $migrationName, $content);

echo "Arquivo de migração criado: " . $migrationName . "\n";
