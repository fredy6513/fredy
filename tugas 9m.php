<?php
/**
 * Edit Data PHP
 * File untuk mengedit data dari database MySQL
 */

// Include file koneksi
require_once 'koneksi_mysql.php';

// Fungsi untuk mendapatkan data berdasarkan ID
function getDataById($table_name, $id) {
    try {
        $conn = koneksiDatabase();
        
        $sql = "SELECT * FROM $table_name WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $data = $result->fetch_assoc();
            $conn->close();
            return $data;
        } else {
            $conn->close();
            return null;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return null;
    }
}

// Fungsi untuk update data
function updateData($table_name, $id, $data) {
    try {
        $conn = koneksiDatabase();
        
        // Bangun query update dinamis
        $set_clause = "";
        $types = "";
        $values = array();
        
        foreach ($data as $field => $value) {
            if ($field !== 'id' && $field !== 'table') {
                $set_clause .= "$field = ?, ";
                $types .= "s"; // Semua dianggap string untuk keamanan
                $values[] = $value;
            }
        }
        
        $set_clause = rtrim($set_clause, ", ");
        $types .= "i"; // Untuk parameter ID
        $values[] = $id;
        
        $sql = "UPDATE $table_name SET $set_clause WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters dinamis
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        return false;
    }
}

// Main program
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Edit Data</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; }";
echo "form { max-width: 600px; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "input[type='text'], input[type='number'], input[type='date'] { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; }";
echo "button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }";
echo "button:hover { background-color: #0056b3; }";
echo ".container { max-width: 800px; margin: 0 auto; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";

if (isset($_GET['id']) && isset($_GET['table'])) {
    $id = $_GET['id'];
    $table_name = $_GET['table'];
    
    // Ambil data yang akan diedit
    $data = getDataById($table_name, $id);
    
    if ($data) {
        echo "<h1>Edit Data - Tabel: $table_name</h1>";
        
        // Form edit
        echo "<form method='POST' action='proses_edit.php'>";
        echo "<input type='hidden' name='id' value='$id'>";
        echo "<input type='hidden' name='table' value='$table_name'>";
        
        foreach ($data as $field => $value) {
            // Skip field id untuk form (tetap sebagai hidden)
            if ($field === 'id') continue;
            
            echo "<div>";
            echo "<label for='$field'>$field:</label>";
            
            // Tentukan tipe input berdasarkan nama field
            if (strpos($field, 'date') !== false || strpos($field, 'tanggal') !== false) {
                echo "<input type='date' id='$field' name='$field' value='$value'>";
            } elseif (strpos($field, 'email') !== false) {
                echo "<input type='email' id='$field' name='$field' value='$value'>";
            } elseif (is_numeric($value) && !is_string($value)) {
                echo "<input type='number' id='$field' name='$field' value='$value'>";
            } else {
                echo "<input type='text' id='$field' name='$field' value='" . htmlspecialchars($value) . "'>";
            }
            
            echo "</div>";
        }
        
        echo "<button type='submit'>Simpan Perubahan</button>";
        echo " <a href='tampil_data.php?table=$table_name' style='margin-left: 10px;'>Batal</a>";
        echo "</form>";
    } else {
        echo "<p>Data tidak ditemukan.</p>";
        echo "<a href='tampil_data.php'>Kembali ke Daftar Tabel</a>";
    }
} else {
    echo "<p>Parameter tidak lengkap.</p>";
    echo "<a href='tampil_data.php'>Kembali ke Daftar Tabel</a>";
}

echo "</div>";
echo "</body>";
echo "</html>";
?>