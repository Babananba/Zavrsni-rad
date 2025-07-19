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
