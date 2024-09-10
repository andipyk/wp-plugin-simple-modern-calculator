document.addEventListener('DOMContentLoaded', () => {
    const calculator = document.getElementById('modern-calculator');
    if (!calculator) return;

    const form = {
        num1: calculator.querySelector('#num1'),
        num2: calculator.querySelector('#num2'),
        operator: calculator.querySelector('#operator'),
        calculate: calculator.querySelector('#calculate'),
        result: calculator.querySelector('#result')
    };

    form.calculate.addEventListener('click', async (e) => {
        e.preventDefault();

        if (!validateInputs()) return;

        try {
            const response = await fetchCalculation();
            handleResponse(response);
        } catch (error) {
            handleError(error);
        }
    });

    function validateInputs() {
        if (!form.num1.value || !form.num2.value || !form.operator.value) {
            displayResult('Please fill in all fields');
            return false;
        }
        return true;
    }

    async function fetchCalculation() {
        const response = await fetch(modernCalculatorData.ajax_url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: modernCalculatorData.action,
                nonce: modernCalculatorData.nonce,
                num1: form.num1.value,
                num2: form.num2.value,
                operator: form.operator.value
            })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        return response.json();
    }

    function handleResponse(data) {
        if (data.success) {
            displayResult(`${data.data.result}`);
        } else {
            displayResult(`Error: ${data.data}`);
        }
    }

    function handleError(error) {
        console.error('Error:', error);
        displayResult('An error occurred while calculating');
    }

    function displayResult(message) {
        form.result.value = message;
    }
});