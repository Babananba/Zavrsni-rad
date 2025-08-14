// ===================
// 1. POMOĆNE FUNKCIJE
// ===================

function decimalToFractionLatex(decimal, tolerance = 1.0E-6) {
    if (Math.abs(decimal) < 1e-10) {
        return "0";
    }

    if (Math.abs(decimal - Math.round(decimal)) < tolerance) {
        return Math.round(decimal).toString();
    }

    const sign = decimal < 0 ? "-" : "";
    decimal = Math.abs(decimal);

    let h1 = 1, h2 = 0;
    let k1 = 0, k2 = 1;
    let b = decimal;

    while (true) {
        const a = Math.floor(b);
        const aux = h1;
        h1 = a * h1 + h2;
        h2 = aux;

        const aux2 = k1;
        k1 = a * k1 + k2;
        k2 = aux2;

        if (k1 === 0) break;

        const approx = h1 / k1;
        if (Math.abs(decimal - approx) < tolerance) break;

        b = 1 / (b - a);
        if (b > 1e6) break;
    }

    if (k1 === 1) {
        return sign + h1;
    }

    if (Math.abs(decimal - (h1 / k1)) < tolerance) {
        return sign + "\\frac{" + h1 + "}{" + k1 + "}";
    }

    return sign + decimal.toFixed(4);
}

function generirajLatexMatricu(matrica, format = 'fraction') {
    let latex = "\\begin{pmatrix}\n";
    const rows = [];

    for (const row of matrica) {
        const escapedRow = row.map(v => {
            return format === 'fraction' ? decimalToFractionLatex(v) : v.toFixed(2);
        });
        rows.push(escapedRow.join(" & "));
    }

    latex += rows.join(" \\\\ \n");
    latex += "\n\\end{pmatrix}";

    return latex;
}

function generirajLatexProsirenuMatricu(a, b) {
    const n = a.length;
    const rows = [];

    for (let i = 0; i < n; i++) {
        const row = [];
        for (let j = 0; j < n; j++) {
            row.push(decimalToFractionLatex(a[i][j]));
        }
        row.push("|");
        for (let j = 0; j < n; j++) {
            row.push(decimalToFractionLatex(b[i][j]));
        }
        rows.push(row.join(" & "));
    }

    return "\\begin{bmatrix}\n" + rows.join(" \\\\ \n") + "\n\\end{bmatrix}";
}

// ===================
// 2. DETERMINANTA
// ===================

function determinantaGauss(matrica, koraci = null) {
    const n = matrica.length;
    const a = matrica.map(row => [...row]);
    let det = 1;
    let swaps = 0;

    const dodajKorak = (opis, a) => {
        if (koraci) {
            koraci.push(`<p><strong>${opis}</strong></p>`);
            koraci.push(`$$ ${generirajLatexMatricu(a, 'fraction')} $$`);
        }
    };

    dodajKorak("Početna matrica:", a);

    for (let i = 0; i < n; i++) {
        let maxRow = i;
        for (let j = i + 1; j < n; j++) {
            if (Math.abs(a[j][i]) > Math.abs(a[maxRow][i])) {
                maxRow = j;
            }
        }

        if (Math.abs(a[maxRow][i]) < 1e-12) {
            dodajKorak("Pivot je nula — determinanta je 0.", a);
            return 0;
        }

        if (i !== maxRow) {
            [a[i], a[maxRow]] = [a[maxRow], a[i]];
            swaps++;
            dodajKorak(`Zamjena redaka ${i + 1} i ${maxRow + 1} (promjena predznaka)`, a);
        }

        for (let j = i + 1; j < n; j++) {
            const f = a[j][i] / a[i][i];
            for (let k = i; k < n; k++) {
                a[j][k] -= f * a[i][k];
            }
            dodajKorak(`Red ${j + 1} = Red ${j + 1} - (${decimalToFractionLatex(f)}) × Red ${i + 1}`, a);
        }
    }

    for (let i = 0; i < n; i++) {
        det *= a[i][i];
    }

    det *= Math.pow(-1, swaps);
    dodajKorak("Završni trokutasti oblik. Računamo produkt dijagonale.", a);

    return det;
}

function determinantaMatrice(matrica) {
    const n = matrica.length;

    if (n === 2) {
        const [[a, b], [c, d]] = matrica;
        return a * d - b * c;
    }

    if (n === 3) {
        const [[a, b, c], [d, e, f], [g, h, i]] = matrica;
        return a * e * i + b * f * g + c * d * h - c * e * g - b * d * i - a * f * h;
    }

    return determinantaGauss(matrica);
}

function generirajPostupakDeterminante(matrica) {
    const n = matrica.length;
    const koraci = [];

    if (n === 2) {
        const [[a, b], [c, d]] = matrica;
        const det = a * d - b * c;

        koraci.push("<p><strong>Računanje determinante 2×2 matrice:</strong></p>");
        koraci.push(`$$ A = ${generirajLatexMatricu(matrica, 'fraction')} $$`);
        koraci.push(`$$ \\det(A) = (${decimalToFractionLatex(a)}) \\cdot (${decimalToFractionLatex(d)}) - (${decimalToFractionLatex(b)}) \\cdot (${decimalToFractionLatex(c)}) = ${decimalToFractionLatex(det)} $$`);
        return koraci;
    }

    if (n === 3) {
        const [[a, b, c], [d, e, f], [g, h, i]] = matrica;
        const positive = a * e * i + b * f * g + c * d * h;
        const negative = c * e * g + b * d * i + a * f * h;
        const det = positive - negative;

        koraci.push("<p><strong>Računanje determinante 3×3 matrice (Sarrusovo pravilo):</strong></p>");
        koraci.push(`$$ A = ${generirajLatexMatricu(matrica, 'fraction')} $$`);
        koraci.push(`$$ \\det(A) = ${decimalToFractionLatex(a)}\\cdot${decimalToFractionLatex(e)}\\cdot${decimalToFractionLatex(i)} + ${decimalToFractionLatex(b)}\\cdot${decimalToFractionLatex(f)}\\cdot${decimalToFractionLatex(g)} + ${decimalToFractionLatex(c)}\\cdot${decimalToFractionLatex(d)}\\cdot${decimalToFractionLatex(h)} - (${decimalToFractionLatex(c)}\\cdot${decimalToFractionLatex(e)}\\cdot${decimalToFractionLatex(g)} + ${decimalToFractionLatex(b)}\\cdot${decimalToFractionLatex(d)}\\cdot${decimalToFractionLatex(i)} + ${decimalToFractionLatex(a)}\\cdot${decimalToFractionLatex(f)}\\cdot${decimalToFractionLatex(h)}) = ${decimalToFractionLatex(det)} $$`);
        return koraci;
    }

    // Za matrice 4x4 i 5x5 koristimo Gaussovu eliminaciju
    const detKoraci = [];
    const det = determinantaGauss(matrica, detKoraci);
    detKoraci.push(`<p><strong>Determinanta: ${decimalToFractionLatex(det)}</strong></p>`);
    return detKoraci;
}

// ===================
// 3. INVERZ MATRICE
// ===================

function gaussJordanInverz(matrica, koraci = []) {
    const n = matrica.length;
    let inverz = Array.from({ length: n }, (_, i) =>
        Array.from({ length: n }, (_, j) => i === j ? 1 : 0)
    );

    const dodajKorak = (opis) => {
        koraci.push(`
            <div class="modal-step">
                <p class="fw-bold mb-1">${opis}</p>
                <div>$$ ${generirajLatexProsirenuMatricu(matrica, inverz)} $$</div>
            </div>
        `);
    };

    dodajKorak("Početna proširena matrica [A | I]");

    for (let i = 0; i < n; i++) {
        // Pivotiranje
        if (Math.abs(matrica[i][i]) < 1e-10) {
            let j = i + 1;
            while (j < n && Math.abs(matrica[j][i]) < 1e-10) j++;
            if (j >= n) return { error: "Matrica nema inverz (determinanta je 0)" };

            [matrica[i], matrica[j]] = [matrica[j], matrica[i]];
            [inverz[i], inverz[j]] = [inverz[j], inverz[i]];
            dodajKorak(`Zamjena redaka ${i + 1} i ${j + 1} (zbog nultog pivota)`);
        }

        // Normalizacija retka
        const pivot = matrica[i][i];
        if (Math.abs(pivot - 1) > 1e-10) {
            for (let j = 0; j < n; j++) {
                matrica[i][j] /= pivot;
                inverz[i][j] /= pivot;
            }
            dodajKorak(`Dijeljenje retka ${i + 1} s ${decimalToFractionLatex(pivot)}`);
        }

        // Eliminacija
        for (let j = 0; j < n; j++) {
            if (j !== i && Math.abs(matrica[j][i]) > 1e-10) {
                const faktor = matrica[j][i];
                for (let k = 0; k < n; k++) {
                    matrica[j][k] -= faktor * matrica[i][k];
                    inverz[j][k] -= faktor * inverz[i][k];
                }
                dodajKorak(`Red ${j + 1} - (${decimalToFractionLatex(faktor)}) × Red ${i + 1}`);
            }
        }
    }

    return { inverz, koraci };
}

function izracunajInverz(matrica) {
    const { inverz, koraci, error } = gaussJordanInverz(matrica.map(row => [...row]), []);
    return error ? error : { inverz, koraci };
}

function generirajPostupakInverza(matrica) {
    const koraci = [];
    const kopijaMatrice = matrica.map(row => [...row]); // Napravimo kopiju da ne mijenjamo original
    const inverz = gaussJordanInverz(kopijaMatrice, koraci);

    if (typeof inverz === 'string') {
        return [inverz];
    }

    // Dodajemo konačni rezultat na kraj postupka
    koraci.push("<p><strong>Konačni inverz matrice:</strong></p>");
    koraci.push(`$$ A^{-1} = ${generirajLatexMatricu(inverz, 'fraction')} $$`);

    return koraci;
}