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
                               onkeypress="validateInput(event)"
                               onblur="validateField(this)"
                               autocomplete="off"
                               placeholder="0"
                               inputmode="numeric">
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

    window.validateField = function (input) {
        const value = input.value.trim();
        if (value === '' || isNaN(parseFraction(value))) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    };

    window.validateInput = function (event) {
        const input = event.target;
        const char = event.key;
        const value = input.value;

        // Reset validation on new input
        input.classList.remove('is-invalid');

        if (!/[0-9\/.\-]/.test(char)) {
            event.preventDefault();
            return;
        }
        if (char === '-' && value.length > 0) {
            event.preventDefault();
            return;
        }
        if (char === '.' && (value.includes('.') || value.includes('/'))) {
            event.preventDefault();
            return;
        }
        if (char === '/' && (value.includes('/') || value.includes('.'))) {
            event.preventDefault();
            return;
        }
    };

    window.calculateMatrix = function () {
        const size = parseInt(document.querySelector('input[name="matrixSize"]').value);
        const matrix = [];
        let hasError = false;

        // Validate all fields first
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

        // Read matrix values
        for (let i = 0; i < size; i++) {
            matrix[i] = [];
            for (let j = 0; j < size; j++) {
                const input = document.querySelector(`input[name="matrix_${i}_${j}"]`);
                matrix[i][j] = parseFraction(input.value.trim());
            }
        }

        // Calculate inverse
        const result = izracunajInverz(matrix);

        // Display results
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

        // Refresh MathJax rendering
        if (typeof MathJax !== 'undefined') {
            MathJax.typesetPromise().catch(err => console.error(err));
        }
    };

    window.resetForm = function () {
        document.querySelectorAll('#matrixForm input[type="text"]').forEach(input => {
            input.value = "";
            input.classList.remove('is-invalid');
        });
        resultsContainer.innerHTML = `
            <div class="alert alert-info">
                Unesite matricu i kliknite "Izračunaj" za rezultate
            </div>
        `;
    };

    function parseFraction(value) {
        value = value.trim();
        if (value === '') return NaN;

        if (value.includes('/')) {
            const [num, den] = value.split('/', 2);
            if (!isNaN(num) && !isNaN(den) && den != 0 && !num.includes('.') && !den.includes('.')) {
                return parseFloat(num) / parseFloat(den);
            }
            return NaN;
        }
        return parseFloat(value) || (value === '0' ? 0 : NaN);
    }

    // Inicijalizacija početne matrice
    generateMatrixInput(2);
});