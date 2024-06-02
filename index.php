<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrica - Determinanta i Inverz</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateInput(event) {
            const char = String.fromCharCode(event.which);
            const value = event.target.value;

            if (!/[0-9.-]/.test(char)) {
                event.preventDefault();
                return;
            }

            if (char === '.' && value.includes('.')) {
                event.preventDefault();
            }

            if (char === '-' && value.length > 0) {
                event.preventDefault();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Matrica - Determinanta i Inverz</h1>
        <form method="post">
            <label for="matrixSize">Odaberite veličinu matrice:</label><br>
            <select id="matrixSize" name="matrixSize" onchange="this.form.submit()">
                <option value="">Odaberite...</option>
                <?php
                for ($size = 2; $size <= 7; $size++) {
                    echo '<option value="' . $size . '"';
                    if (isset($_POST['matrixSize']) && $_POST['matrixSize'] == $size) {
                        echo ' selected';
                    }
                    echo '>' . $size . 'x' . $size . '</option>';
                }
                ?>
            </select>
        </form>
        
        <?php
        if (isset($_POST['matrixSize'])) {
            $size = intval($_POST['matrixSize']);
            echo '<form method="post">';
            echo '<input type="hidden" name="matrixSize" value="' . $size . '">';
        
            $bracketSize = $size * 50;
        
            $openingBracket = '<span class="opening-bracket" style="font-size: ' . $bracketSize . 'px;">(</span>';
            $closingBracket = '<span class="closing-bracket" style="font-size: ' . $bracketSize . 'px;">)</span>';

            echo '<div class="input-matrix-container">';
            echo '<span class="label">A = </span>';
            echo $openingBracket;
        
            echo '<table class="input-matrix">';
            for ($i = 0; $i < $size; $i++) {
                echo '<tr>';
                for ($j = 0; $j < $size; $j++) {
                    $value = isset($_POST['matrix'][$i][$j]) ? $_POST['matrix'][$i][$j] : '';
                    echo '<td><input type="text" name="matrix[' . $i . '][' . $j . ']" value="' . htmlspecialchars($value) . '" required onkeypress="validateInput(event)"></td>';
                }
                echo '</tr>';
            }
            echo '</table>';
        
            echo $closingBracket;
            echo '</div>';
        
            echo '<button type="submit">Izračunaj</button>';
            echo '</form>';
        }
    
        if (isset($_POST['matrix'])) {
            include 'matrix_functions.php';
            $matrix = $_POST['matrix'];

            foreach ($matrix as &$row) {
                foreach ($row as &$value) {
                    $value = floatval($value);
                }
            }
            
            $det = determinantaMatrice($matrix);
            echo "<h2>Determinanta matrice:</h2>";
            echo "<p><span style='font-weight: bold; font-style: italic;'>detA</span> = $det</p>";

            $inverz = izracunajInverz($matrix);
            echo "<h2>Inverz matrice:</h2>";
            if (is_string($inverz)) {
                echo "<p>$inverz</p>";
            } else {
                $bracketSize = $size * 50;

                $openingBracket = '<span class="opening-bracket" style="font-size: ' . $bracketSize . 'px;">(</span>';
                $closingBracket = '<span class="closing-bracket" style="font-size: ' . $bracketSize . 'px;">)</span>';

                echo '<div class="input-matrix-container inverse-matrix-container">';
                echo '<span class="label">A<sup>-1</sup> = </span>';
                echo $openingBracket;

                echo '<table class="input-matrix">';
                foreach ($inverz as $row) {
                    echo '<tr>';
                    foreach ($row as $value) {
                        echo '<td>' . htmlspecialchars($value) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';

                echo $closingBracket;
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
