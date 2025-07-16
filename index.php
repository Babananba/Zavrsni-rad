<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Matrica - Determinanta i Inverz</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
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

            if (!/[0-9.-]/.test(char)) event.preventDefault();
            if (char === '.' && value.includes('.')) event.preventDefault();
            if (char === '-' && value.length > 0) event.preventDefault();
        }

        function resetForm() {
            const inputs = document.querySelectorAll('#matrixForm input[type="text"]');
            inputs.forEach(input => input.value = "");
            document.getElementById("determinantContainer").innerHTML = "";
            document.getElementById("originalMatrixLatex").innerHTML = "";
            document.getElementById("inverseContainer").innerHTML = "";
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Matrica - Determinanta i Inverz</h1>

    <!-- Izbor veličine matrice -->
    <form id="matrixSizeForm" method="post" onsubmit="return validateForm()">
        <label for="matrixSize">Odaberite veličinu matrice:</label><br />
        <select id="matrixSize" name="matrixSize" onchange="this.form.submit()">
            <option value="">Odaberite...</option>
            <?php for ($size = 2; $size <= 5; $size++): ?>
                <option value="<?= $size ?>" <?= (isset($_POST['matrixSize']) && $_POST['matrixSize'] == $size) ? 'selected' : '' ?>>
                    <?= $size ?>x<?= $size ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>

    <?php if (!empty($_POST['matrixSize'])):
        $size = (int)$_POST['matrixSize'];
    ?>
        <!-- Unos matrice -->
        <form id="matrixForm" method="post">
            <input type="hidden" name="matrixSize" value="<?= $size ?>">
            <div class="input-matrix-container">
                <label>Unesite elemente matrice A:</label><br>
                <table class="input-matrix">
                    <?php for ($i = 0; $i < $size; $i++): ?>
                        <tr>
                            <?php for ($j = 0; $j < $size; $j++):
                                $value = $_POST['matrix'][$i][$j] ?? '';
                            ?>
                                <td><input type="text" name="matrix[<?= $i ?>][<?= $j ?>]" value="<?= htmlspecialchars($value) ?>"
                                           required onkeypress="validateInput(event)" autocomplete="off"></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </table>
            </div>
            <button type="submit">Izračunaj</button>
        </form>
    <?php endif; ?>

    <?php
    if (!empty($_POST['matrix'])):
        include 'matrix_functions.php';

        $matrix = array_map(fn($row) => array_map('floatval', $row), $_POST['matrix']);
        $det = determinantaMatrice($matrix);
        $inverz = izracunajInverz($matrix);
    ?>
        <!-- Prikaz determinante -->
        <div id="determinantContainer">
            <h2>Determinanta matrice:</h2>
            <p style="font-weight: bold; font-style: italic; text-align:center;">detA = <?= $det ?></p>
        </div>

        <!-- Prikaz unesene matrice -->
        <div id="originalMatrixLatex" style="text-align:center; margin-top: 20px;">
            <h3>Unesena matrica A:</h3>
            <p>$$ A = <?= generirajLatexMatricu($matrix) ?> $$</p>
        </div>

        <!-- Inverz matrice -->
        <div id="inverseContainer" style="text-align:center; margin-top: 20px;">
            <h2>Inverz matrice:</h2>
            <?php if (is_string($inverz)): ?>
                <p><?= $inverz ?></p>
            <?php else: ?>
                <p>$$ A^{-1} = <?= generirajLatexMatricu($inverz, 'fraction') ?> $$</p>
            <?php endif; ?>
            <button type="button" onclick="resetForm()">Resetiraj</button>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
