<?php
declare(strict_types=1);

$today = new DateTime('today');
$todayIso = $today->format('Y-m-d');
$todayDisplay = $today->format('l, F j, Y');

$dobInput = '';
$errorMessage = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['btn']) || isset($_POST['dob']))) {
    $rawDob = isset($_POST['dob']) ? (string) $_POST['dob'] : '';
    $dobInput = trim($rawDob);

    if ($dobInput === '') {
        $errorMessage = 'Please select your date of birth to calculate your age.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dobInput)) {
        $errorMessage = 'Please enter a valid date in YYYY-MM-DD format.';
    } else {
        $birthDate = DateTime::createFromFormat('!Y-m-d', $dobInput);
        $parseErrors = DateTime::getLastErrors();
        $hasParseErrors = is_array($parseErrors) && ($parseErrors['warning_count'] > 0 || $parseErrors['error_count'] > 0);

        if ($birthDate === false || $hasParseErrors || $birthDate->format('Y-m-d') !== $dobInput) {
            $errorMessage = 'The selected date is not a valid calendar date. Please try again.';
        } elseif ($birthDate > $today) {
            $errorMessage = 'Date of birth cannot be in the future. Please select today or an earlier date.';
        } else {
            // Core PHP DateTime age calculation
            $age = $today->diff($birthDate);
            $years = (int) $age->y;
            $months = (int) $age->m;
            $days = (int) $age->d;
            $totalDays = (int) $age->days;

            $isBirthday = ($birthDate->format('m-d') === $today->format('m-d'));
            $isBornToday = ($totalDays === 0);

            if ($isBornToday) {
                $friendlyMessage = 'Welcome to the world! Today marks the very first day of your life story.';
            } elseif ($isBirthday) {
                $friendlyMessage = sprintf(
                    'Happy Birthday! Wishing you an unforgettable year %d ahead.',
                    $years
                );
            } elseif ($years === 0) {
                $friendlyMessage = 'Every week brings remarkable firsts — savor each moment of this early chapter.';
            } elseif ($years < 13) {
                $friendlyMessage = 'Curiosity and wonder lead the way — enjoy every discovery along your journey.';
            } elseif ($years < 20) {
                $friendlyMessage = 'Bold horizons and defining chapters are unfolding right before you.';
            } elseif ($years < 40) {
                $friendlyMessage = 'In the prime of building, exploring, and turning ambitions into lasting milestones.';
            } elseif ($years < 65) {
                $friendlyMessage = 'Rich in perspective and momentum — every year adds depth to your story.';
            } else {
                $friendlyMessage = 'A remarkable legacy of wisdom, resilience, and moments well lived.';
            }

            $yearUnit = ($years === 1) ? 'year' : 'years';
            $monthUnit = ($months === 1) ? 'month' : 'months';
            $dayUnit = ($days === 1) ? 'day' : 'days';
            $totalDaysLabel = ($totalDays === 1) ? 'day lived' : 'days lived';

            $result = [
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'totalDays' => $totalDays,
                'totalDaysFormatted' => number_format($totalDays),
                'totalDaysLabel' => $totalDaysLabel,
                'birthDisplay' => $birthDate->format('F j, Y'),
                'birthWeekday' => $birthDate->format('l'),
                'friendlyMessage' => $friendlyMessage,
                'isBirthday' => $isBirthday,
                'isBornToday' => $isBornToday,
                'summaryText' => sprintf(
                    'Your age is %d %s, %d %s, and %d %s old.',
                    $years,
                    $yearUnit,
                    $months,
                    $monthUnit,
                    $days,
                    $dayUnit
                ),
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discover Your Age — Modern Age Calculator</title>
    <meta name="description" content="Every moment tells a story. Discover your exact age in years, months, days, and total days lived.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #060a14;
            --bg-mid: #0b1324;
            --bg-edge: #0f172a;
            --glass-bg: rgba(15, 23, 42, 0.64);
            --glass-border: rgba(255, 255, 255, 0.11);
            --glass-highlight: rgba(255, 255, 255, 0.07);
            --surface-tile: rgba(255, 255, 255, 0.045);
            --surface-tile-hover: rgba(255, 255, 255, 0.075);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --accent-primary: #38bdf8;
            --accent-strong: #0284c7;
            --accent-deep: #0369a1;
            --accent-teal: #2dd4bf;
            --error-bg: rgba(239, 68, 68, 0.12);
            --error-border: rgba(248, 113, 113, 0.35);
            --error-text: #fecaca;
            --radius-card: 24px;
            --radius-tile: 16px;
            --radius-control: 12px;
            --ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--text-primary);
            background:
                radial-gradient(circle at 18% 18%, rgba(14, 165, 233, 0.16) 0%, transparent 45%),
                radial-gradient(circle at 82% 80%, rgba(45, 212, 191, 0.12) 0%, transparent 45%),
                linear-gradient(145deg, var(--bg-deep) 0%, var(--bg-mid) 52%, var(--bg-edge) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 4vw, 3rem) clamp(1rem, 3vw, 2rem);
            position: relative;
            overflow-x: hidden;
            line-height: 1.5;
        }

        /* Floating ambient background gradients */
        .ambient-canvas {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(72px);
            opacity: 0.5;
            will-change: transform;
        }

        .ambient-orb--one {
            width: clamp(260px, 34vw, 480px);
            height: clamp(260px, 34vw, 480px);
            background: radial-gradient(circle, rgba(56, 189, 248, 0.36), rgba(14, 165, 233, 0) 70%);
            top: -10%;
            left: -8%;
            animation: floatOrbOne 18s ease-in-out infinite alternate;
        }

        .ambient-orb--two {
            width: clamp(240px, 30vw, 440px);
            height: clamp(240px, 30vw, 440px);
            background: radial-gradient(circle, rgba(45, 212, 191, 0.28), rgba(45, 212, 191, 0) 70%);
            bottom: -12%;
            right: -6%;
            animation: floatOrbTwo 22s ease-in-out infinite alternate;
        }

        .ambient-orb--three {
            width: clamp(180px, 22vw, 320px);
            height: clamp(180px, 22vw, 320px);
            background: radial-gradient(circle, rgba(59, 130, 246, 0.22), rgba(59, 130, 246, 0) 70%);
            top: 48%;
            right: 22%;
            animation: floatOrbThree 26s ease-in-out infinite alternate;
        }

        @keyframes floatOrbOne {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            100% { transform: translate3d(48px, 36px, 0) scale(1.08); }
        }

        @keyframes floatOrbTwo {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            100% { transform: translate3d(-44px, -32px, 0) scale(1.06); }
        }

        @keyframes floatOrbThree {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            100% { transform: translate3d(28px, -38px, 0) scale(0.94); }
        }

        @keyframes pageEntrance {
            0% {
                opacity: 0;
                transform: translate3d(0, 22px, 0);
            }
            100% {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        @keyframes resultReveal {
            0% {
                opacity: 0;
                transform: translate3d(0, 14px, 0);
            }
            100% {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }

        .page-shell {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 720px;
            animation: pageEntrance 0.7s var(--ease-out-expo) both;
        }

        /* Glassmorphism Calculator Card */
        .calculator-card {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-card);
            padding: clamp(1.5rem, 4vw, 2.75rem);
            box-shadow:
                0 28px 70px -18px rgba(2, 6, 23, 0.78),
                inset 0 1px 0 var(--glass-highlight);
        }

        /* Header & Current Date */
        .card-header {
            margin-bottom: 2rem;
        }

        .date-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            font-size: 0.8125rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
            letter-spacing: 0.01em;
        }

        .date-bar__item {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .date-bar__icon {
            width: 15px;
            height: 15px;
            color: var(--accent-primary);
            flex-shrink: 0;
        }

        .date-bar__value {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .hero-title {
            font-size: clamp(2rem, 4.5vw, 2.85rem);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -0.025em;
            color: var(--text-primary);
            text-wrap: balance;
            margin-bottom: 0.75rem;
        }

        .hero-subtitle {
            font-size: clamp(0.95rem, 1.8vw, 1.0625rem);
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 54ch;
            font-weight: 400;
        }

        /* Form Section */
        .age-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
        }

        .input-label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            letter-spacing: 0.01em;
        }

        .input-label svg {
            width: 16px;
            height: 16px;
            color: var(--accent-primary);
        }

        .date-input-wrap {
            position: relative;
        }

        .date-input {
            width: 100%;
            min-height: 52px;
            padding: 0.85rem 1rem;
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-primary);
            background: rgba(6, 10, 20, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: var(--radius-control);
            color-scheme: dark;
            transition:
                border-color 0.2s var(--ease-out-expo),
                box-shadow 0.2s var(--ease-out-expo),
                background-color 0.2s var(--ease-out-expo),
                transform 0.2s var(--ease-out-expo);
        }

        .date-input:hover {
            border-color: rgba(255, 255, 255, 0.26);
            background: rgba(6, 10, 20, 0.72);
        }

        .date-input:focus {
            outline: none;
            border-color: var(--accent-primary);
            background: rgba(6, 10, 20, 0.82);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.16);
            transform: translateY(-1px);
        }

        .date-input::-webkit-calendar-picker-indicator {
            cursor: pointer;
            opacity: 0.85;
            padding: 4px;
            border-radius: 6px;
            transition: opacity 0.18s ease, background-color 0.18s ease;
        }

        .date-input::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
            background-color: rgba(255, 255, 255, 0.08);
        }

        .input-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        /* Action Buttons */
        .button-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            min-height: 48px;
            padding: 0.75rem 1.5rem;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: var(--radius-control);
            border: 1px solid transparent;
            cursor: pointer;
            white-space: nowrap;
            text-decoration: none;
            transition:
                transform 0.18s var(--ease-out-expo),
                box-shadow 0.2s var(--ease-out-expo),
                background-color 0.2s var(--ease-out-expo),
                border-color 0.2s var(--ease-out-expo),
                color 0.2s var(--ease-out-expo);
        }

        .btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .btn:focus-visible {
            outline: 2px solid var(--accent-primary);
            outline-offset: 2px;
        }

        .btn--primary {
            color: #ffffff;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            box-shadow: 0 10px 24px -8px rgba(14, 165, 233, 0.55);
        }

        .btn--primary:hover {
            background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -8px rgba(56, 189, 248, 0.62);
        }

        .btn--primary:active {
            transform: translateY(0) scale(0.985);
            box-shadow: 0 6px 14px -6px rgba(14, 165, 233, 0.5);
        }

        .btn--secondary {
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.13);
        }

        .btn--secondary:hover {
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.22);
            transform: translateY(-2px);
        }

        .btn--secondary:active {
            transform: translateY(0) scale(0.985);
        }

        /* Inline Alert / Error State */
        .error-banner {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding: 0.95rem 1.1rem;
            border-radius: var(--radius-control);
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            color: var(--error-text);
            font-size: 0.9rem;
            line-height: 1.5;
            animation: resultReveal 0.35s var(--ease-out-expo) both;
        }

        .error-banner[hidden] {
            display: none;
        }

        .error-banner svg {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
            margin-top: 1px;
            color: #f87171;
        }

        /* Results Section */
        .results-section {
            margin-top: 2rem;
            padding-top: 1.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            animation: resultReveal 0.5s var(--ease-out-expo) both;
        }

        .results-section[hidden] {
            display: none;
        }

        .results-meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            margin-bottom: 1.25rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .results-meta__birth {
            color: var(--text-secondary);
        }

        .results-meta__birth strong {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* Three Separate Cards: YEARS | MONTHS | DAYS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: var(--surface-tile);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: var(--radius-tile);
            padding: 1.35rem 1rem;
            text-align: center;
            transition:
                transform 0.2s var(--ease-out-expo),
                background-color 0.2s var(--ease-out-expo),
                border-color 0.2s var(--ease-out-expo);
            animation: resultReveal 0.55s var(--ease-out-expo) both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.04s; }
        .stat-card:nth-child(2) { animation-delay: 0.11s; }
        .stat-card:nth-child(3) { animation-delay: 0.18s; }

        .stat-card:hover {
            transform: translateY(-3px);
            background: var(--surface-tile-hover);
            border-color: rgba(56, 189, 248, 0.3);
        }

        .stat-card__value {
            display: block;
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: clamp(2.15rem, 5vw, 3.1rem);
            font-weight: 600;
            line-height: 1.05;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .stat-card:first-child .stat-card__value {
            color: var(--accent-primary);
        }

        .stat-card:nth-child(2) .stat-card__value {
            color: #7dd3fc;
        }

        .stat-card:nth-child(3) .stat-card__value {
            color: var(--accent-teal);
        }

        .stat-card__label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* Summary & Total Days Lived Strip */
        .insight-panel {
            margin-top: 1.25rem;
            padding: 1.15rem 1.25rem;
            border-radius: var(--radius-tile);
            background: rgba(14, 165, 233, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.2);
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            animation: resultReveal 0.6s var(--ease-out-expo) 0.16s both;
        }

        .insight-panel__top {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.5rem 1rem;
        }

        .insight-panel__summary {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        .insight-panel__days {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            font-variant-numeric: tabular-nums;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--accent-teal);
        }

        .insight-panel__message {
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        .insight-panel__message svg {
            width: 17px;
            height: 17px;
            color: var(--accent-primary);
            flex-shrink: 0;
            margin-top: 3px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 600px) {
            .calculator-card {
                border-radius: 20px;
                padding: 1.35rem 1.15rem;
            }

            .button-row {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .stat-card {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem 1.25rem;
                text-align: left;
            }

            .stat-card__value {
                margin-bottom: 0;
                font-size: 2rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-canvas" aria-hidden="true">
        <div class="ambient-orb ambient-orb--one"></div>
        <div class="ambient-orb ambient-orb--two"></div>
        <div class="ambient-orb ambient-orb--three"></div>
    </div>

    <main class="page-shell">
        <section class="calculator-card" aria-labelledby="page-heading">
            <header class="card-header">
                <div class="date-bar">
                    <span class="date-bar__item">
                        <svg class="date-bar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Today:</span>
                        <time class="date-bar__value" datetime="<?php echo htmlspecialchars($todayIso, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($todayDisplay, ENT_QUOTES, 'UTF-8'); ?>
                        </time>
                    </span>
                </div>

                <h1 id="page-heading" class="hero-title">Discover Your Age</h1>
                <p class="hero-subtitle">
                    Every moment tells a story. Discover exactly how long your journey has been.
                </p>
            </header>

            <form id="age-form" class="age-form" method="post" action="" novalidate>
                <div class="input-group">
                    <label for="dob" class="input-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>Enter your date of birth</span>
                    </label>

                    <div class="date-input-wrap">
                        <input
                            type="date"
                            id="dob"
                            name="dob"
                            class="date-input"
                            max="<?php echo htmlspecialchars($todayIso, ENT_QUOTES, 'UTF-8'); ?>"
                            value="<?php echo htmlspecialchars($dobInput, ENT_QUOTES, 'UTF-8'); ?>"
                            required
                            aria-describedby="dob-hint"
                        >
                    </div>

                    <p id="dob-hint" class="input-hint">
                        Select any date up to <?php echo htmlspecialchars($todayDisplay, ENT_QUOTES, 'UTF-8'); ?>.
                    </p>
                </div>

                <div class="button-row">
                    <button type="submit" name="btn" value="calculate" class="btn btn--primary" id="calculate-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                            <line x1="8" y1="6" x2="16" y2="6"></line>
                            <line x1="16" y1="14" x2="16" y2="18"></line>
                            <path d="M16 10h.01"></path>
                            <path d="M12 10h.01"></path>
                            <path d="M8 10h.01"></path>
                            <path d="M12 14h.01"></path>
                            <path d="M8 14h.01"></path>
                            <path d="M12 18h.01"></path>
                            <path d="M8 18h.01"></path>
                        </svg>
                        <span>Calculate Age</span>
                    </button>

                    <button type="button" class="btn btn--secondary" id="reset-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <path d="M3 3v5h5"></path>
                        </svg>
                        <span>Reset</span>
                    </button>
                </div>
            </form>

            <div
                id="error-banner"
                class="error-banner"
                role="alert"
                aria-live="assertive"
                <?php echo ($errorMessage === '') ? 'hidden' : ''; ?>
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span id="error-text"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <?php if ($result !== null) : ?>
                <section id="results-section" class="results-section" aria-live="polite" aria-label="Calculated Age Result">
                    <div class="results-meta">
                        <span class="results-meta__birth">
                            Born on <strong><?php echo htmlspecialchars($result['birthWeekday'], ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars($result['birthDisplay'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        </span>
                    </div>

                    <div class="stats-grid">
                        <article class="stat-card">
                            <span class="stat-card__value" data-count-target="<?php echo (int) $result['years']; ?>">
                                <?php echo (int) $result['years']; ?>
                            </span>
                            <span class="stat-card__label">YEARS</span>
                        </article>

                        <article class="stat-card">
                            <span class="stat-card__value" data-count-target="<?php echo (int) $result['months']; ?>">
                                <?php echo (int) $result['months']; ?>
                            </span>
                            <span class="stat-card__label">MONTHS</span>
                        </article>

                        <article class="stat-card">
                            <span class="stat-card__value" data-count-target="<?php echo (int) $result['days']; ?>">
                                <?php echo (int) $result['days']; ?>
                            </span>
                            <span class="stat-card__label">DAYS</span>
                        </article>
                    </div>

                    <div class="insight-panel">
                        <div class="insight-panel__top">
                            <p class="insight-panel__summary">
                                <?php echo htmlspecialchars($result['summaryText'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <span class="insight-panel__days">
                                <span data-count-target="<?php echo (int) $result['totalDays']; ?>" data-format-number="true">
                                    <?php echo htmlspecialchars($result['totalDaysFormatted'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php echo htmlspecialchars($result['totalDaysLabel'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        </div>

                        <p class="insight-panel__message">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 16.8l-6.2 4.5 2.4-7.4L2 9.4h7.6z"></path>
                            </svg>
                            <span><?php echo htmlspecialchars($result['friendlyMessage'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </p>
                    </div>
                </section>
            <?php endif; ?>
        </section>
    </main>

    <script>
        (function () {
            'use strict';

            var form = document.getElementById('age-form');
            var dobInput = document.getElementById('dob');
            var resetBtn = document.getElementById('reset-btn');
            var errorBanner = document.getElementById('error-banner');
            var errorText = document.getElementById('error-text');
            var resultsSection = document.getElementById('results-section');
            var maxDate = dobInput ? dobInput.getAttribute('max') : '';

            function showError(message) {
                if (!errorBanner || !errorText) {
                    return;
                }
                errorText.textContent = message;
                errorBanner.hidden = false;
                if (resultsSection) {
                    resultsSection.hidden = true;
                }
            }

            function clearError() {
                if (!errorBanner || !errorText) {
                    return;
                }
                errorText.textContent = '';
                errorBanner.hidden = true;
            }

            function isValidCalendarDate(dateStr) {
                if (!/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                    return false;
                }
                var parts = dateStr.split('-');
                var year = parseInt(parts[0], 10);
                var month = parseInt(parts[1], 10);
                var day = parseInt(parts[2], 10);
                var probe = new Date(Date.UTC(year, month - 1, day));
                return (
                    probe.getUTCFullYear() === year &&
                    probe.getUTCMonth() === month - 1 &&
                    probe.getUTCDate() === day
                );
            }

            if (form && dobInput) {
                form.addEventListener('submit', function (event) {
                    var value = dobInput.value.trim();

                    if (value === '') {
                        event.preventDefault();
                        showError('Please select your date of birth to calculate your age.');
                        dobInput.focus();
                        return;
                    }

                    if (!isValidCalendarDate(value)) {
                        event.preventDefault();
                        showError('The selected date is not a valid calendar date. Please try again.');
                        dobInput.focus();
                        return;
                    }

                    if (maxDate && value > maxDate) {
                        event.preventDefault();
                        showError('Date of birth cannot be in the future. Please select today or an earlier date.');
                        dobInput.focus();
                        return;
                    }

                    clearError();
                });

                dobInput.addEventListener('input', function () {
                    if (errorBanner && !errorBanner.hidden) {
                        clearError();
                    }
                });
            }

            if (resetBtn && dobInput) {
                resetBtn.addEventListener('click', function () {
                    dobInput.value = '';
                    clearError();

                    if (resultsSection) {
                        resultsSection.hidden = true;
                    }

                    if (window.history && typeof window.history.replaceState === 'function') {
                        window.history.replaceState(null, '', window.location.pathname);
                    }

                    dobInput.focus();
                });
            }

            // Smooth count-up animation for calculated numbers
            var prefersReducedMotion =
                window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (!prefersReducedMotion && resultsSection && !resultsSection.hidden) {
                var counters = resultsSection.querySelectorAll('[data-count-target]');
                var duration = 550;

                counters.forEach(function (el) {
                    var target = parseInt(el.getAttribute('data-count-target') || '0', 10);
                    var shouldFormat = el.getAttribute('data-format-number') === 'true';

                    if (isNaN(target) || target <= 0) {
                        return;
                    }

                    var startTime = null;

                    function step(timestamp) {
                        if (startTime === null) {
                            startTime = timestamp;
                        }
                        var elapsed = timestamp - startTime;
                        var progress = Math.min(elapsed / duration, 1);
                        var eased = 1 - Math.pow(1 - progress, 3);
                        var current = Math.round(target * eased);

                        el.textContent = shouldFormat ? current.toLocaleString() : String(current);

                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        } else {
                            el.textContent = shouldFormat ? target.toLocaleString() : String(target);
                        }
                    }

                    window.requestAnimationFrame(step);
                });
            }
        })();
    </script>
</body>
</html>
