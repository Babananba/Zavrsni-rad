<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inverz Matrice Gaussovom metodom eliminacije</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- MathJax -->
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
      const char = event.key;
      const value = event.target.value;
      if (!/[0-9\/.\-]/.test(char)) { event.preventDefault(); return; }
      if (char === '-' && value.length > 0) { event.preventDefault(); return; }
      if (char === '.' && (value.includes('.') || value.includes('/'))) { event.preventDefault(); return; }
      if (char === '/' && (value.includes('/') || value.includes('.'))) { event.preventDefault(); return; }
    }

    function resetForm() {
      document.querySelectorAll('#matrixForm input[type="text"]').forEach(i => i.value = "");
      ['determinantContainer','originalMatrixLatex','inverseContainer'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = "";
      });
    }
  </script>
</head>
<body class="d-flex flex-column min-vh-100">

  <!-- Header -->
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#">MatrixTool</a>
      </div>
    </nav>
  </header>

  <!-- Main content -->
  <main class="container mt-5 pt-5 pb-5 flex-fill">
    <div class="text-center mb-4">
      <h4>Odaberite dimenziju matrice:</h4>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-6">
        <form id="matrixSizeForm" method="post" onsubmit="return validateForm()" class="mb-4">
          <div class="input-group">
            <label class="input-group-text" for="matrixSize">Dimenzija</label>
            <select id="matrixSize" name="matrixSize" class="form-select" onchange="this.form.submit()">
              <option value="">Odaberite...</option>
              <?php for ($n = 2; $n <= 5; $n++): ?>
                <option value="<?= $n ?>" <?= (isset($_POST['matrixSize']) && $_POST['matrixSize']==$n)?'selected':'' ?>><?= $n ?>×<?= $n ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </form>

        <?php if (!empty($_POST['matrixSize'])):
          $size = (int)$_POST['matrixSize'];
        ?>
        <div class="mb-3 text-center fw-bold">Unesite matricu:</div>
        <form id="matrixForm" method="post">
          <input type="hidden" name="matrixSize" value="<?= $size ?>">
          <div class="table-responsive mb-3">
            <table class="table table-borderless mx-auto" style="width:auto;">
              <?php for ($i = 0; $i < $size; $i++): ?>
              <tr>
                <?php for ($j = 0; $j < $size; $j++):
                  $val = $_POST['matrix'][$i][$j] ?? '';
                ?>
                <td>
                  <input type="text" name="matrix[<?= $i ?>][<?= $j ?>]"
                         class="form-control text-center"
                         style="width: 60px"
                         value="<?= htmlspecialchars($val) ?>" required
                         onkeypress="validateInput(event)" autocomplete="off">
                </td>
                <?php endfor; ?>
              </tr>
              <?php endfor; ?>
            </table>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-success me-2">Izračunaj</button>
            <button type="button" class="btn btn-secondary" onclick="resetForm()">Resetiraj</button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <?php
    if (!empty($_POST['matrix'])):
      include 'matrix_functions.php';
      function parseFraction(string $v): float {
        $v = trim($v);
        if (strpos($v, '/')!==false) {
          [$num,$den] = explode('/', $v,2);
          if (is_numeric($num)&&is_numeric($den)&&$den!=0
              &&strpos($num,'.')===false&&strpos($den,'.')===false) {
            return floatval($num)/floatval($den);
          }
          return 0;
        }
        return floatval($v);
      }
      $matrix = array_map(fn($r)=>array_map('parseFraction',$r),$_POST['matrix']);
      $size = count($matrix);
      if ($size === 2) {
          $a = $matrix[0][0];
          $b = $matrix[0][1];
          $c = $matrix[1][0];
          $d = $matrix[1][1];
          $det = $a * $d - $b * $c;
      } elseif ($size === 3) {
          [$a,$b,$c] = $matrix[0];
          [$d,$e,$f] = $matrix[1];
          [$g,$h,$i] = $matrix[2];
          $det = $a*$e*$i + $b*$f*$g + $c*$d*$h - $c*$e*$g - $b*$d*$i - $a*$f*$h;
      } else {
          $det = determinantaMatrice($matrix);
      }
      $inverz = izracunajInverz($matrix);
    ?>

    <div class="row justify-content-center mt-5 mb-5">
      <div class="col-lg-8 text-center">
        <div id="originalMatrixLatex" class="mt-3">
          <h5>Unesena matrica A:</h5>
          <p>$$ A = <?= generirajLatexMatricu($matrix) ?> $$</p>
        </div>

        <div id="inverseContainer" class="mt-3">
          <h5>Inverz matrice:</h5>
          <?php if (is_string($inverz)): ?>
            <div class="alert alert-danger p-2 w-50 mx-auto small"><?= htmlspecialchars($inverz) ?></div>
          <?php else: ?>
            <p>$$ A^{-1} = <?= generirajLatexMatricu($inverz,'fraction') ?> $$</p>
          <?php endif; ?>
        </div>

        <!-- Gumb za modal -->
        <?php if (!is_string($inverz)): ?>
          <button class="btn btn-outline-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalInverz">
            Prikaži postupak za inverz
          </button>
        <?php else: ?>
          <button class="btn btn-outline-danger mt-3" data-bs-toggle="modal" data-bs-target="#modalDet">
            Prikaži računanje determinante
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php endif; ?>
  </main>

  <!-- Footer -->
  <footer class="bg-dark text-white py-3 mt-auto">
    <div class="container text-center">
      <small>© 2025 MatrixTool • <a href="#" class="text-decoration-none text-light">O projektu</a> • <a href="#" class="text-decoration-none text-light">Kontakt</a></small>
    </div>
  </footer>

  <!-- Modal: Inverz -->
  <div class="modal fade" id="modalInverz" tabindex="-1" aria-labelledby="modalInverzLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Postupak računanja inverza</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
        </div>
        <div class="modal-body">
          <?php if (!empty($matrix) && !is_string($inverz)): ?>
            <p>$$ A \,|\, I $$</p>
            <?= generirajPostupakInverza($matrix); ?>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal: Determinanta -->
  <div class="modal fade" id="modalDet" tabindex="-1" aria-labelledby="modalDetLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Računanje determinante</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
        </div>
        <div class="modal-body">
          <?php if (!empty($matrix)): ?>
            <?= generirajPostupakDeterminante($matrix); ?>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zatvori</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
