<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrica - Determinanta i Inverz</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function validateForm() {
            const selectSize = document.getElementById("matrixSize");
            if (selectSize.value === "") {
                alert("Molimo odaberite veličinu matrice.");
                return false;
            }
            return true;
        }

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

        function resetForm() {
            const form = document.getElementById("matrixForm");
            const inputs = form.getElementsByTagName("input");

            for (let input of inputs) {
                if (input.type === "text") {
                    input.value = "";
                }
            }

            document.getElementById("determinantContainer").innerHTML = "";
            document.getElementById("inverseContainer").innerHTML = "";
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Matrica - Determinanta i Inverz</h1>
        <form id="matrixSizeForm" method="post" onsubmit="return validateForm()">
            <label for="matrixSize">Odaberite veličinu matrice:</label><br>
            <select id="matrixSize" name="matrixSize" onchange="this.form.submit()">
                <option value="">Odaberite...</option>
                <?php
                for ($size = 2; $size <= 5; $size++) {
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
        if (isset($_POST['matrixSize']) && $_POST['matrixSize'] !== "") {
            $size = intval($_POST['matrixSize']);
            echo '<form id="matrixForm" method="post">';
            echo '<input type="hidden" name="matrixSize" value="' . $size . '">';

            $bracketSize = $size * 45;

            $openingBracket = '<span class="opening-bracket" style="font-size: ' . $bracketSize . 'px;">(</span>';
            $closingBracket = '<span class="closing-bracket" style="font-size: ' . $bracketSize . 'px;">)</span>';

            echo '<div class="input-matrix-container matrix-container-' . $size . '">';
            echo '<span class="label">A = </span>';
            echo $openingBracket;

            echo '<table class="input-matrix">';
            for ($i = 0; $i < $size; $i++) {
                echo '<tr>';
                for ($j = 0; $j < $size; $j++) {
                    $value = isset($_POST['matrix'][$i][$j]) ? $_POST['matrix'][$i][$j] : '';
                    echo '<td><input type="text" name="matrix[' . $i . '][' . $j . ']" value="' . htmlspecialchars($value) . '" required onkeypress="validateInput(event)" autocomplete="off"></td>';
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
            
            echo "<div id='determinantContainer' style='text-align: center;'>";
            $det = determinantaMatrice($matrix);
            echo "<h2>Determinanta matrice:</h2>";
            echo "<p style='text-align: center;'><span style='font-weight: bold; font-style: italic;'>detA</span> = $det</p>";
            echo "</div>";
            
            echo "<div id='inverseContainer'>";
            $inverz = izracunajInverz($matrix);
            echo "<h2 style='text-align: center;'>Inverz matrice:</h2>";
            if (is_string($inverz)) {
                echo "<p>$inverz</p>";
                echo '<button type="button" onclick="resetForm()">Resetiraj</button>';
            } else {
                $bracketSize = $size * 50;

                $openingBracket = '<span class="opening-bracket" style="font-size: ' . $bracketSize . 'px;">(</span>';
                $closingBracket = '<span class="closing-bracket" style="font-size: ' . $bracketSize . 'px;">)</span>';

                echo '<div class="input-matrix-container inverse-matrix-container matrix-container-' . $size . '">';
                echo '<span class="label">A<sup>-1</sup> = </span>';
                echo $openingBracket;

                echo '<table class="input-matrix">';
                for ($i = 0; $i < count($inverz); $i++) {
                    echo '<tr>';
                    for ($j = 0; $j < count($inverz[$i]); $j++) {
                        $value = number_format($inverz[$i][$j], 2); 
                        echo '<td><input type="text" value="' . htmlspecialchars($value) . '" readonly></td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';

                echo $closingBracket;
                echo '</div>';

                echo '<button type="button" onclick="resetForm()">Resetiraj</button>';
                echo '</div>';
            }
        }
        ?>
        
    </div>
</body>
</html>
