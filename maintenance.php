<?php
require_once 'config.php';

$state = ['on' => false, 'target' => null];
if (file_exists(MAINTENANCE_STATE_FILE)) {
    $decoded = json_decode(@file_get_contents(MAINTENANCE_STATE_FILE), true);
    if (is_array($decoded)) {
        $state = array_merge($state, $decoded);
    }
}

$target = $state['target'] ?: '2025-12-31T17:00:00-05:00';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turnpage</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        
        .logo {
            margin-bottom: 3rem;
        }
        
        .logo img {
            width: 120px;
            height: auto;
        }
        
        h1 {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }
        
        .subtitle {
            font-size: 1.1rem;
            color: #a0a0a0;
            margin-bottom: 3rem;
            line-height: 1.5;
        }
        
        .countdown {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
        }
        
        .time-unit {
            text-align: center;
            min-width: 80px;
        }
        
        .time-number {
            font-size: 2.5rem;
            font-weight: 600;
            color: #007aff;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .time-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        
        .subscribe-form {
            margin-top: 2rem;
        }
        
        .form-group {
            display: flex;
            max-width: 400px;
            margin: 0 auto;
            gap: 0.5rem;
        }
        
        .email-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #333;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .email-input:focus {
            outline: none;
            border-color: #007aff;
        }
        
        .email-input::placeholder {
            color: #666;
        }
        
        .subscribe-btn {
            padding: 0.75rem 1.5rem;
            background: #007aff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }
        
        .subscribe-btn:hover {
            background: #0056cc;
        }
        
        .subscribe-btn:disabled {
            background: #444;
            cursor: not-allowed;
        }
        
        .disclaimer {
            font-size: 0.8rem;
            color: #666;
            margin-top: 1rem;
        }
        
        .message {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            display: none;
        }
        
        .message.success {
            background: rgba(52, 199, 89, 0.1);
            color: #34c759;
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        .message.error {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 1rem;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .countdown {
                gap: 1rem;
            }
            
            .time-number {
                font-size: 2rem;
            }
            
            .form-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="20" y="40" width="80" height="20" rx="4" fill="#007aff"/>
                <rect x="20" y="70" width="80" height="20" rx="4" fill="#007aff"/>
                <rect x="30" y="30" width="60" height="60" rx="8" fill="none" stroke="#333" stroke-width="2"/>
            </svg>
        </div>
        
        <h1>Turnpage</h1>
        <p class="subtitle">We're performing scheduled upgrades to deliver a faster, more reliable experience.</p>
        
        <div class="countdown">
            <div class="time-unit">
                <span class="time-number" id="days">0</span>
                <span class="time-label">Days</span>
            </div>
            <div class="time-unit">
                <span class="time-number" id="hours">0</span>
                <span class="time-label">Hours</span>
            </div>
            <div class="time-unit">
                <span class="time-number" id="minutes">0</span>
                <span class="time-label">Minutes</span>
            </div>
            <div class="time-unit">
                <span class="time-number" id="seconds">0</span>
                <span class="time-label">Seconds</span>
            </div>
        </div>
        
        <form class="subscribe-form" id="subscribeForm">
            <div class="form-group">
                <input type="email" class="email-input" name="email" placeholder="Enter your email" required>
                <button type="submit" class="subscribe-btn" id="subscribeBtn">Subscribe</button>
            </div>
            <p class="disclaimer">Address is used only for maintenance updates. Unsubscribe any time.</p>
            <div class="message" id="message"></div>
        </form>
    </div>
    
    <script>
        const targetDate = new Date('<?php echo $target; ?>');
        
        function updateCountdown() {
            const now = new Date();
            const diff = targetDate - now;
            
            if (diff <= 0) {
                document.getElementById('days').textContent = '0';
                document.getElementById('hours').textContent = '0';
                document.getElementById('minutes').textContent = '0';
                document.getElementById('seconds').textContent = '0';
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = days;
            document.getElementById('hours').textContent = hours;
            document.getElementById('minutes').textContent = minutes;
            document.getElementById('seconds').textContent = seconds;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
        
        // Subscription form
        document.getElementById('subscribeForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const btn = document.getElementById('subscribeBtn');
            const message = document.getElementById('message');
            
            btn.disabled = true;
            btn.textContent = 'Subscribing...';
            message.style.display = 'none';
            
            try {
                const response = await fetch('subscribe.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                message.textContent = result.message;
                message.className = 'message ' + (result.success ? 'success' : 'error');
                message.style.display = 'block';
                
                if (result.success) {
                    form.reset();
                }
            } catch (error) {
                message.textContent = 'Network error. Please try again.';
                message.className = 'message error';
                message.style.display = 'block';
            }
            
            btn.disabled = false;
            btn.textContent = 'Subscribe';
        });
    </script>
</body>
</html>