<?php
/**
 * Plugin Name: Modern Calculator
 * Description: A modern calculator plugin for WordPress using ES6+ and Fetch API
 * Version: 2.1
 * Author: Your Name
 */

declare(strict_types=1);

namespace ModernCalculatorPlugin;

class ModernCalculator {
    private const SHORTCODE = 'modern_calculator';
    private const AJAX_ACTION = 'modern_calculator_action';
    private const NONCE_NAME = 'modern_calculator_nonce';

    public function __construct() {
        add_shortcode(self::SHORTCODE, [$this, 'renderCalculator']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_action('wp_ajax_' . self::AJAX_ACTION, [$this, 'handleAjax']);
        add_action('wp_ajax_nopriv_' . self::AJAX_ACTION, [$this, 'handleAjax']);
    }

    public function renderCalculator(): string {
        ob_start();
        ?>
        <div id="modern-calculator" class="calculator">
            <div class="calculator-display">
                <input type="text" id="result" readonly>
            </div>
            <div class="calculator-keys">
                <input type="number" id="num1" required placeholder="Enter number">
                <select id="operator">
                    <option value="add">+</option>
                    <option value="subtract">-</option>
                    <option value="multiply">×</option>
                    <option value="divide">÷</option>
                </select>
                <input type="number" id="num2" required placeholder="Enter number">
                <button id="calculate" class="key-equal">=</button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueueScripts(): void {
        wp_enqueue_script('modern-calculator', plugin_dir_url(__FILE__) . 'calculator.js', [], '2.1', true);
        wp_localize_script('modern-calculator', 'modernCalculatorData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce(self::NONCE_NAME),
            'action' => self::AJAX_ACTION
        ]);
    }

    public function enqueueStyles(): void {
        wp_enqueue_style('modern-calculator-style', plugin_dir_url(__FILE__) . 'calculator-style.css', [], '1.0');
    }

    public function handleAjax(): void {
        try {
            check_ajax_referer(self::NONCE_NAME, 'nonce');

            $num1 = $this->validateNumber('num1');
            $num2 = $this->validateNumber('num2');
            $operator = $this->validateOperator();

            $result = $this->calculate($num1, $num2, $operator);
            wp_send_json_success(['result' => $result]);
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    private function validateNumber(string $key): float {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_FLOAT);
        if ($value === false || $value === null) {
            throw new \InvalidArgumentException("Invalid input for {$key}");
        }
        return $value;
    }

    private function validateOperator(): string {
        $operator = filter_input(INPUT_POST, 'operator', FILTER_SANITIZE_STRING);
        if (!in_array($operator, ['add', 'subtract', 'multiply', 'divide'])) {
            throw new \InvalidArgumentException('Invalid operator');
        }
        return $operator;
    }

    private function calculate(float $num1, float $num2, string $operator): string {
        switch ($operator) {
            case 'add':
                return (string)($num1 + $num2);
            case 'subtract':
                return (string)($num1 - $num2);
            case 'multiply':
                return (string)($num1 * $num2);
            case 'divide':
                if ($num2 === 0.0) {
                    throw new \DivisionByZeroError('Division by zero');
                }
                return (string)($num1 / $num2);
            default:
                throw new \InvalidArgumentException('Invalid operator');
        }
    }
}

new ModernCalculator();