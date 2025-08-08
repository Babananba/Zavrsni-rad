<?php

function ispisiMatricu($matrica)
{
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

function determinantaMatrice($matrica)
{
    $n = count($matrica);

    if ($n == 1) return $matrica[0][0];

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

function izracunajInverz($matrica)
{
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
            for ($k = 0; $k < $n; $k++) {
                $matrica[$j][$k] -= $matrica[$i][$k] * $faktor;
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
            $inverz[$i][$j] = round($inverz[$i][$j], 2);
        }
    }

    return $inverz;
}

function decimalToFractionLatex($decimal, $tolerance = 1.0E-6)
{
    if (abs($decimal - round($decimal)) < $tolerance) {
        return (string)round($decimal);
    }

    $sign = $decimal < 0 ? "-" : "";
    $decimal = abs($decimal);

    $h1 = 1;
    $h2 = 0;
    $k1 = 0;
    $k2 = 1;
    $b = $decimal;

    while (true) {
        $a = floor($b);
        $aux = $h1;
        $h1 = $a * $h1 + $h2;
        $h2 = $aux;

        $aux = $k1;
        $k1 = $a * $k1 + $k2;
        $k2 = $aux;

        if ($k1 == 0) break;

        $approx = $h1 / $k1;
        if (abs($decimal - $approx) < $tolerance) {
            break;
        }

        $b = 1 / ($b - $a);
        if ($b > 1e6) break;
    }

    if ($k1 == 1) {
        return $sign . $h1;
    }

    if (abs($decimal - ($h1 / $k1)) < $tolerance) {
        return $sign . "\\frac{" . $h1 . "}{" . $k1 . "}";
    }

    return $sign . round($decimal, 4);
}

function generirajLatexMatricu($matrica, $format = 'fraction')
{
    $latex = "\\begin{pmatrix}\n";
    $rows = [];

    foreach ($matrica as $row) {
        $escapedRow = array_map(function ($v) use ($format) {
            if ($format === 'fraction') {
                return decimalToFractionLatex($v);
            } else {
                return number_format($v, 2);
            }
        }, $row);
        
        $rows[] = implode(" & ", $escapedRow);
    }

    $latex .= implode(" \\\\ \n", $rows);
    $latex .= "\n\\end{pmatrix}";

    return $latex;
}

function generirajPostupakInverza($matrica)
{
    $n = count($matrica);
    $original = $matrica;
    $inverz = [];

    for ($i = 0; $i < $n; $i++) {
        $inverz[$i] = array_fill(0, $n, 0);
        $inverz[$i][$i] = 1;
    }

    $koraci = [];
    $dodajKorak = function($opis, $a, $b) use (&$koraci) {
        $koraci[] = "<p><strong>{$opis}</strong></p>";
        $koraci[] = '$$ ' . generirajLatexProsirenuMatricu($a, $b) . ' $$';
    };

    $dodajKorak("Početna proširena matrica [A | I]:", $matrica, $inverz);

    for ($i = 0; $i < $n; $i++) {
        // Pivotiranje ako je potrebno
        $pivot = $matrica[$i][$i];
        if (abs($pivot) < 1e-12) {
            for ($j = $i + 1; $j < $n; $j++) {
                if (abs($matrica[$j][$i]) > abs($pivot)) {
                    [$matrica[$i], $matrica[$j]] = [$matrica[$j], $matrica[$i]];
                    [$inverz[$i], $inverz[$j]] = [$inverz[$j], $inverz[$i]];
                    $dodajKorak("Zamjena redaka {$i}+1 i {$j}+1 jer je pivot element ≈ 0:", $matrica, $inverz);
                    $pivot = $matrica[$i][$i];
                    break;
                }
            }
        }

        if ($pivot == 0) {
            $dodajKorak("Pivot element je nula — matrica nema inverz.", $matrica, $inverz);
            break;
        }

        // Skaliranje reda da pivot postane 1
        if (abs($pivot - 1.0) > 1e-10) {
            for ($j = 0; $j < $n; $j++) {
                $matrica[$i][$j] /= $pivot;
                $inverz[$i][$j] /= $pivot;
            }
            $opis = "Dijelimo red " . ($i + 1) . " s " . decimalToFractionLatex($pivot);
            $dodajKorak($opis, $matrica, $inverz);
        }

        // Eliminacija u ostalim redovima
        for ($j = 0; $j < $n; $j++) {
            if ($j == $i) continue;
            $faktor = $matrica[$j][$i];
            if (abs($faktor) > 1e-10) {
                for ($k = 0; $k < $n; $k++) {
                    $matrica[$j][$k] -= $faktor * $matrica[$i][$k];
                    $inverz[$j][$k] -= $faktor * $inverz[$i][$k];
                }
                $opis = "Red " . ($j + 1) . " = Red " . ($j + 1) . " - (" . decimalToFractionLatex($faktor) . ") × Red " . ($i + 1);
                $dodajKorak($opis, $matrica, $inverz);
            }
        }
    }

    return implode("\n", $koraci);
}


function generirajLatexProsirenuMatricu($a, $b)
{
    $n = count($a);
    $rows = [];

    for ($i = 0; $i < $n; $i++) {
        $row = [];
        for ($j = 0; $j < $n; $j++) {
            $row[] = decimalToFractionLatex($a[$i][$j]);
        }
        $row[] = "|";
        for ($j = 0; $j < $n; $j++) {
            $row[] = decimalToFractionLatex($b[$i][$j]);
        }
        $rows[] = implode(" & ", $row);
    }

    return "\\begin{bmatrix}\n" . implode(" \\\\ \n", $rows) . "\n\\end{bmatrix}";
}

function generirajPostupakDeterminante($matrica)
{
    $n = count($matrica);
    $koraci = [];

    if ($n === 2) {
        $a = $matrica[0][0];
        $b = $matrica[0][1];
        $c = $matrica[1][0];
        $d = $matrica[1][1];
        $det = $a * $d - $b * $c;

        $koraci[] = "<p><strong>Računanje determinante 2×2 matrice:</strong></p>";
        $koraci[] = '$$ A = ' . generirajLatexMatricu($matrica, 'fraction') . ' $$';
        $koraci[] = '$$ \det(A) = (' . decimalToFractionLatex($a) . ') \cdot (' . decimalToFractionLatex($d) . ') - (' . decimalToFractionLatex($b) . ') \cdot (' . decimalToFractionLatex($c) . ') = ' . decimalToFractionLatex($det) . ' $$';
        return implode("\n", $koraci);
    }

    if ($n === 3) {
        [$a,$b,$c] = $matrica[0];
        [$d,$e,$f] = $matrica[1];
        [$g,$h,$i] = $matrica[2];

        $positive = $a*$e*$i + $b*$f*$g + $c*$d*$h;
        $negative = $c*$e*$g + $b*$d*$i + $a*$f*$h;
        $det = $positive - $negative;

        $koraci[] = "<p><strong>Računanje determinante 3×3 matrice (Sarrusovo pravilo):</strong></p>";
        $koraci[] = '$$ A = ' . generirajLatexMatricu($matrica, 'fraction') . ' $$';

        $koraci[] = '$$ \det(A) = ' .
            decimalToFractionLatex($a) . '\cdot' . decimalToFractionLatex($e) . '\cdot' . decimalToFractionLatex($i) . ' + ' .
            decimalToFractionLatex($b) . '\cdot' . decimalToFractionLatex($f) . '\cdot' . decimalToFractionLatex($g) . ' + ' .
            decimalToFractionLatex($c) . '\cdot' . decimalToFractionLatex($d) . '\cdot' . decimalToFractionLatex($h) .
            ' - (' .
            decimalToFractionLatex($c) . '\cdot' . decimalToFractionLatex($e) . '\cdot' . decimalToFractionLatex($g) . ' + ' .
            decimalToFractionLatex($b) . '\cdot' . decimalToFractionLatex($d) . '\cdot' . decimalToFractionLatex($i) . ' + ' .
            decimalToFractionLatex($a) . '\cdot' . decimalToFractionLatex($f) . '\cdot' . decimalToFractionLatex($h) .
            ')' . ' = ' . decimalToFractionLatex($det) . ' $$';

        return implode("\n", $koraci);
    }

    // Inače koristi Gaussovu eliminaciju
    return generirajPostupakDeterminante_Gauss($matrica);
}

function generirajPostupakDeterminante_Gauss($matrica)
{
    $n = count($matrica);
    $a = $matrica;
    $koraci = [];
    $det = 1;
    $swaps = 0;

    $dodajKorak = function($opis, $a) use (&$koraci) {
        $koraci[] = "<p><strong>{$opis}</strong></p>";
        $koraci[] = '$$ ' . generirajLatexMatricu($a, 'fraction') . ' $$';
    };

    $dodajKorak("Početna matrica:", $a);

    for ($i = 0; $i < $n; $i++) {
        $maxRow = $i;
        for ($j = $i + 1; $j < $n; $j++) {
            if (abs($a[$j][$i]) > abs($a[$maxRow][$i])) {
                $maxRow = $j;
            }
        }

        if (abs($a[$maxRow][$i]) < 1e-12) {
            $dodajKorak("Pivot je nula — determinanta je 0.", $a);
            return implode("\n", $koraci) . "<p><strong>Determinanta: 0</strong></p>";
        }

        if ($i != $maxRow) {
            [$a[$i], $a[$maxRow]] = [$a[$maxRow], $a[$i]];
            $swaps++;
            $dodajKorak("Zamjena redaka " . ($i + 1) . " i " . ($maxRow + 1) . " (promjena predznaka)", $a);
        }

        for ($j = $i + 1; $j < $n; $j++) {
            $f = $a[$j][$i] / $a[$i][$i];
            for ($k = $i; $k < $n; $k++) {
                $a[$j][$k] -= $f * $a[$i][$k];
            }
            $opis = "Red " . ($j + 1) . " = Red " . ($j + 1) . " - (" . decimalToFractionLatex($f) . ") × Red " . ($i + 1);
            $dodajKorak($opis, $a);
        }
    }

    for ($i = 0; $i < $n; $i++) {
        $det *= $a[$i][$i];
    }
    $det *= pow(-1, $swaps);

    $dodajKorak("Završni trokutasti oblik. Računamo produkt dijagonale.", $a);
    $koraci[] = "<p><strong>Determinanta: " . decimalToFractionLatex($det) . "</strong></p>";

    return implode("\n", $koraci);
}
