document.addEventListener('DOMContentLoaded', function () {
    // Inicijalizacija
    const matrixInputContainer = document.getElementById('matrixInputContainer');
    const resultsContainer = document.getElementById('resultsContainer');
    const modalInverzBody = document.getElementById('modalInverzBody');
    const modalDetBody = document.getElementById('modalDetBody');

    // Globalno dostupne funkcije
    window.generateMatrixInput = function (size) {
        let html = `
            <div class="matrix-container matrix-${size}x${size}">
                <h5 class="text-center mb-3">Matrica ${size}×${size}</h5>
                <form id="matrixForm">
                    <input type="hidden" name="matrixSize" value="${size}">
                    <div class="matrix-table-container">
                        <table class="matrix-table">
        `;

        for (let i = 0; i < size; i++) {
            html += '<tr>';
            for (let j = 0; j < size; j++) {
                html += `
                    <td>
                        <input type="text" name="matrix_${i}_${j}"
                               class="matrix-input"
                               required
                               onkeydown="handleKeyDown(event)"
                               oninput="validateNumberInput(this)"
                               autocomplete="off"
                               placeholder="0"
                               inputmode="decimal">
                    </td>
                `;
            }
            html += '</tr>';
        }

        html += `
                        </table>
                    </div>
                </form>
            </div>
        `;

        matrixInputContainer.innerHTML = html;
        resultsContainer.innerHTML = `
            <div class="alert alert-info">
                Unesite matricu i kliknite "Izračunaj" za rezultate
            </div>
        `;

        // Fokusiraj prvo polje
        requestAnimationFrame(() => {
            const firstInput = document.querySelector('.matrix-input');
            if (firstInput) {
                firstInput.focus();
                firstInput.select();
            }
        });
    };

    // Validacija unosa
    window.validateNumberInput = function (input) {
        const value = input.value;

        // Resetiraj stanje
        input.classList.remove('is-invalid', 'negative');

        // Provjeri prazno polje
        if (value === '') {
            return;
        }

        // Glavni regex pattern koji dopušta:
        // 1. Negativne brojeve
        // 2. Decimale u brojniku
        // 3. Decimale u nazivniku
        // 4. Decimale u oba
        const validPattern = /^-?(\d+\.?\d*|\d*\.\d+)(\/(\d+\.?\d*|\d*\.\d+))?$/;

        // Dodatna pravila
        const isValid = validPattern.test(value) &&
            (value.match(/\./g) || []).length <= 2 && // Max 2 točke (1 u brojniku, 1 u nazivniku)
            (value.match(/\//g) || []).length <= 1 &&  // Max 1 kosa crta
            !value.endsWith('.') &&                    // Ne može završavati točkom
            !value.endsWith('/') &&                    // Ne može završavati kosom crtom
            value !== '-';                             // Ne može biti samo minus

        if (!isValid) {
            input.classList.add('is-invalid');
            return;
        }

        // Posebna provjera za razlomke
        if (value.includes('/')) {
            const [numerator, denominator] = value.split('/');

            // Ne može biti prazan brojnik ili nazivnik
            if (numerator === '' || denominator === '') {
                input.classList.add('is-invalid');
                return;
            }
        }

        // Označi negativne brojeve
        if (value.startsWith('-')) {
            input.classList.add('negative');
        }
    };

    window.handleKeyDown = function (event) {
        const input = event.target;
        const char = event.key;
        const value = input.value;
        const cursorPos = input.selectionStart;
        const selectionLength = input.selectionEnd - cursorPos;

        // Dozvoli kontrolne tipke
        if ([8, 9, 13, 16, 27, 37, 38, 39, 40].includes(event.keyCode)) {
            return true;
        }

        // Posebna logika za minus
        if (char === '-') {
            if (cursorPos === 0 || selectionLength === value.length) {
                setTimeout(() => {
                    input.value = value.startsWith('-') ?
                        value.substring(1) : '-' + value;
                    validateNumberInput(input);
                }, 10);
            }
            event.preventDefault();
            return false;
        }

        // Dozvoli samo brojeve, točku i kosu crtu
        if (!/[0-9./]/.test(char)) {
            event.preventDefault();
            return false;
        }

        // Provjeri decimalne točke
        if (char === '.') {
            const parts = value.split('/');
            const currentPart = cursorPos <= value.indexOf('/') || !value.includes('/') ? 0 : 1;

            // Provjeri da li već postoji točka u trenutnom dijelu (brojnik/nazivnik)
            if (parts[currentPart] && parts[currentPart].includes('.')) {
                event.preventDefault();
                return false;
            }
        }

        // Provjeri kose crte
        if (char === '/') {
            if (value.includes('/') || value.endsWith('.')) {
                event.preventDefault();
                return false;
            }
        }

        return true;
    };

    // Izračun matrice
    window.calculateMatrix = function () {
        const size = parseInt(document.querySelector('input[name="matrixSize"]').value);
        const matrix = [];
        let hasError = false;

        // Validacija svih polja
        document.querySelectorAll('.matrix-input').forEach(input => {
            const value = input.value.trim();
            if (value === '' || isNaN(parseFraction(value))) {
                input.classList.add('is-invalid');
                hasError = true;
            }
        });

        if (hasError) {
            resultsContainer.innerHTML = `
                <div class="alert alert-danger">
                    Pogreška: Molimo unesite ispravne brojeve u sva polja!
                </div>
            `;
            return;
        }

        // Očitaj vrijednosti matrice
        for (let i = 0; i < size; i++) {
            matrix[i] = [];
            for (let j = 0; j < size; j++) {
                const input = document.querySelector(`input[name="matrix_${i}_${j}"]`);
                matrix[i][j] = parseFraction(input.value.trim());
            }
        }

        // Izračunaj inverz
        const result = izracunajInverz(matrix);

        // Prikaži rezultate
        displayResults(matrix, result);
    };

    // Prikaz rezultata
    function displayResults(matrix, result) {
        let resultsHtml = `
            <div class="result-card">
                <h5 class="fw-bold">Unesena matrica</h5>
                <div class="text-center my-3">
                    <p>$$ A = ${generirajLatexMatricu(matrix)} $$</p>
                </div>
                
                <h5 class="fw-bold mt-4">Inverz matrice</h5>
        `;

        if (typeof result === 'string') {
            resultsHtml += `
                <div class="alert alert-danger">${result}</div>
                <button class="btn btn-outline-secondary mt-2" data-bs-toggle="modal" data-bs-target="#modalDet">
                    Prikaži postupak za determinantu
                </button>
            `;

            // Calculate determinant steps
            const detSteps = generirajPostupakDeterminante(matrix);
            modalDetBody.innerHTML = `
                <div class="modal-step-container">
                    ${detSteps.join('')}
                </div>
            `;
        } else {
            resultsHtml += `
                <div class="text-center my-3">
                    <p>$$ A^{-1} = ${generirajLatexMatricu(result.inverz)} $$</p>
                </div>
                <button class="btn btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalInverz">
                    Prikaži postupak računanja
                </button>
            `;

            // Prepare inversion steps modal
            modalInverzBody.innerHTML = `
                <div class="modal-step-container">
                    ${result.koraci.join('')}
                    <hr>
                    <h5>Konačni rezultat:</h5>
                    <p>$$ A^{-1} = ${generirajLatexMatricu(result.inverz)} $$</p>
                </div>
            `;
        }

        resultsContainer.innerHTML = resultsHtml;

        // Osvježi MathJax renderiranje
        if (typeof MathJax !== 'undefined') {
            MathJax.typesetPromise().catch(err => console.error(err));
        }
    };

    // Resetiranje forme
    window.resetForm = function () {
        document.querySelectorAll('#matrixForm input[type="text"]').forEach(input => {
            input.value = "";
            input.classList.remove('is-invalid', 'negative');
        });
        resultsContainer.innerHTML = `
            <div class="alert alert-info">
                Unesite matricu i kliknite "Izračunaj" za rezultate
            </div>
        `;
    };

    // Parsiranje razlomaka i decimalnih brojeva
    function parseFraction(value) {
        value = value.trim();
        if (value === '') return NaN;

        const isNegative = value.startsWith('-');
        if (isNegative) {
            value = value.substring(1);
        }

        if (value.includes('/')) {
            const [num, den] = value.split('/');
            const numerator = parseFloat(num);
            const denominator = parseFloat(den);

            if (isNaN(numerator) || isNaN(denominator) || denominator === 0) {
                return NaN;
            }
            return (isNegative ? -1 : 1) * (numerator / denominator);
        }

        const number = parseFloat(value);
        return isNaN(number) ? NaN : (isNegative ? -number : number);
    }

    // Inicijalizacija početne matrice
    generateMatrixInput(2);
});