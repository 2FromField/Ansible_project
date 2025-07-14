<?php
$servername = "localhost";
$username = "app_user";  // Utiliser app_user
$password = "pwd";  // Le mot de passe correct pour app_user
$dbname = "app_db";  // Le nom de la base de données

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}
echo "Connexion réussie";
?>