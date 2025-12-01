<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier si l'admin existe déjà
        $existingAdmin = Admin::where('email', 'admin@glory-event.com')->first();

        if ($existingAdmin) {
            $this->command->info('L\'administrateur existe déjà. Mise à jour...');
            $existingAdmin->update([
                'name' => 'Glory Event Super Admin',
                'password' => Hash::make('admin123'),
                'role' => Admin::ROLE_ADMIN,
            ]);
        } else {
            // Créer le nouvel administrateur - SEULEMENT LES CHAMPS QUI EXISTENT
            Admin::create([
                'name' => 'Glory Event Super Admin',
                'email' => 'admin@glory-event.com',
                'password' => Hash::make('admin123'),
                'role' => Admin::ROLE_ADMIN,
                // Supprimer les champs qui n'existent pas dans la table
                // 'is_active' => true, // N'existe pas
                // 'last_login_at' => null, // N'existe pas
                // 'last_login_ip' => null, // N'existe pas
                // 'email_verified_at' => now(), // N'existe pas
                // Les champs created_at et updated_at sont automatiques
            ]);
            $this->command->info('Administrateur créé avec succès!');
        }

        // Créer également un éditeur de test
        $existingEditor = Admin::where('email', 'editor@glory-event.com')->first();
        
        if ($existingEditor) {
            $existingEditor->update([
                'name' => 'Glory Event Editor',
                'password' => Hash::make('editor123'),
                'role' => Admin::ROLE_EDITOR,
            ]);
        } else {
            Admin::create([
                'name' => 'Glory Event Editor',
                'email' => 'editor@glory-event.com',
                'password' => Hash::make('editor123'),
                'role' => Admin::ROLE_EDITOR,
                // Supprimer les champs qui n'existent pas
            ]);
            $this->command->info('Éditeur créé avec succès!');
        }

        $this->command->info('========================================');
        $this->command->info('COMPTES ADMIN CRÉÉS AVEC SUCCÈS');
        $this->command->info('========================================');
        $this->command->info('SUPER ADMINISTRATEUR:');
        $this->command->info('Email: admin@glory-event.com');
        $this->command->info('Mot de passe: admin123');
        $this->command->info('Rôle: ' . Admin::$roles[Admin::ROLE_ADMIN]);
        $this->command->info('----------------------------------------');
        $this->command->info('ÉDITEUR:');
        $this->command->info('Email: editor@glory-event.com');
        $this->command->info('Mot de passe: editor123');
        $this->command->info('Rôle: ' . Admin::$roles[Admin::ROLE_EDITOR]);
        $this->command->info('========================================');
    }
}