<?php

// Funkcija za ispis matrice
function ispisiMatricu($matrica) {
    if (is_array($matrica)) {
        $n = count($matrica);
        echo '<table class="matrix-table">';
        for ($i = 0; $i < $n; $i++) {
            echo '<tr>';
            for ($j = 0; $j < $n; $j++) {
                echo '<td>' . htmlspecialchars($matrica[$i][$j]) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo htmlspecialchars($matrica);
    }
}

// Funkcija za izračun determinante matrice
function determinantaMatrice($matrica) {
    $n = count($matrica);

    if ($n == 1) {
        return $matrica[0][0];
    }

    $determinanta = 0;
    for ($i = 0; $i < $n; $i++) {
        $minor = array();
        for ($j = 1; $j < $n; $j++) {
            $temp = array();
            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $temp[] = $matrica[$j][$k];
                }
            }
            $minor[] = $temp;
        }
        $determinanta += pow(-1, $i) * $matrica[0][$i] * determinantaMatrice($minor);
    }

    return $determinanta;
}

// Funkcija za izračun inverza matrice
function izracunajInverz($matrica) {
    $n = count($matrica);
    $determinanta = determinantaMatrice($matrica);
    if ($determinanta == 0) {
        return "Matrica je singularna, inverz ne postoji.";
    }
    $inverz = array();
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $inverz[$i][$j] = ($i == $j) ? 1 : 0;
        }
    }
    for ($i = 0; $i < $n; $i++) {
        $maxRedak = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if (abs($matrica[$j][$i]) > abs($matrica[$maxRedak][$i])) {
                $maxRedak = $j;
            }
        }
        if ($maxRedak != $i) {
            $tmp = $matrica[$i];
            $matrica[$i] = $matrica[$maxRedak];
            $matrica[$maxRedak] = $tmp;
            $tmp = $inverz[$i];
            $inverz[$i] = $inverz[$maxRedak];
            $inverz[$maxRedak] = $tmp;
        }
        $pivotalniElement = $matrica[$i][$i];
        for ($j = $i + 1; $j < $n; $j++) {
            $faktor = $matrica[$j][$i] / $pivotalniElement;
            for ($k = $i; $k < $n; $k++) {
                $matrica[$j][$k] -= $matrica[$i][$k] * $faktor;
            }
            for ($k = 0; $k < $n; $k++) {
                $inverz[$j][$k] -= $inverz[$i][$k] * $faktor;
            }
        }
    }
    for ($i = $n - 1; $i >= 0; $i--) {
        $pivotalniElement = $matrica[$i][$i];
        for ($j = 0; $j < $i; $j++) {
            $faktor = $matrica[$j][$i] / $pivotalniElement;
            for ($k = 0; $k < $n; $k++) {
                $inverz[$j][$k] -= $inverz[$i][$k] * $faktor;
            }
        }
    }
    for ($i = 0; $i < $n; $i++) {
        $pivotalniElement = $matrica[$i][$i];
        for ($j = 0; $j < $n; $j++) {
            $inverz[$i][$j] /= $pivotalniElement;
        }
    }
    return $inverz;
}
?>
