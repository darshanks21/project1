/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useEffect, useMemo, useRef, useState } from 'react';
import {
  AlertCircle,
  Calculator,
  Calendar,
  Clock,
  RotateCcw,
  Sparkles,
} from 'lucide-react';

interface AgeResult {
  years: number;
  months: number;
  days: number;
  totalDays: number;
  birthDisplay: string;
  birthWeekday: string;
  friendlyMessage: string;
  summaryText: string;
  totalDaysLabel: string;
}

function toIsoDate(date: Date): string {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function parseLocalDate(iso: string): { year: number; month: number; day: number } | null {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(iso)) {
    return null;
  }
  const [yStr, mStr, dStr] = iso.split('-');
  const year = Number(yStr);
  const month = Number(mStr);
  const day = Number(dStr);

  const probe = new Date(Date.UTC(year, month - 1, day));
  if (
    probe.getUTCFullYear() !== year ||
    probe.getUTCMonth() !== month - 1 ||
    probe.getUTCDate() !== day
  ) {
    return null;
  }

  return { year, month, day };
}

function calculateExactAge(dobIso: string, todayDate: Date): AgeResult | { error: string } {
  const trimmed = dobIso.trim();
  if (trimmed === '') {
    return { error: 'Please select your date of birth to calculate your age.' };
  }

  const parsedBirth = parseLocalDate(trimmed);
  if (!parsedBirth) {
    return { error: 'The selected date is not a valid calendar date. Please try again.' };
  }

  const todayYear = todayDate.getFullYear();
  const todayMonth = todayDate.getMonth() + 1;
  const todayDay = todayDate.getDate();
  const todayIso = toIsoDate(todayDate);

  if (trimmed > todayIso) {
    return {
      error: 'Date of birth cannot be in the future. Please select today or an earlier date.',
    };
  }

  let years = todayYear - parsedBirth.year;
  let months = todayMonth - parsedBirth.month;
  let days = todayDay - parsedBirth.day;

  let cursorYear = todayYear;
  let cursorMonth = todayMonth; // 1..12

  while (days < 0) {
    cursorMonth -= 1;
    if (cursorMonth < 1) {
      cursorMonth = 12;
      cursorYear -= 1;
    }
    const daysInCursorMonth = new Date(Date.UTC(cursorYear, cursorMonth, 0)).getUTCDate();
    days += daysInCursorMonth;
    months -= 1;
  }

  if (months < 0) {
    months += 12;
    years -= 1;
  }

  const birthUtcMs = Date.UTC(parsedBirth.year, parsedBirth.month - 1, parsedBirth.day);
  const todayUtcMs = Date.UTC(todayYear, todayMonth - 1, todayDay);
  const totalDays = Math.round((todayUtcMs - birthUtcMs) / 86_400_000);

  const isBirthday = parsedBirth.month === todayMonth && parsedBirth.day === todayDay;
  const isBornToday = totalDays === 0;

  let friendlyMessage: string;
  if (isBornToday) {
    friendlyMessage =
      'Welcome to the world! Today marks the very first day of your life story.';
  } else if (isBirthday) {
    friendlyMessage = `Happy Birthday! Wishing you an unforgettable year ${years} ahead.`;
  } else if (years === 0) {
    friendlyMessage =
      'Every week brings remarkable firsts — savor each moment of this early chapter.';
  } else if (years < 13) {
    friendlyMessage =
      'Curiosity and wonder lead the way — enjoy every discovery along your journey.';
  } else if (years < 20) {
    friendlyMessage = 'Bold horizons and defining chapters are unfolding right before you.';
  } else if (years < 40) {
    friendlyMessage =
      'In the prime of building, exploring, and turning ambitions into lasting milestones.';
  } else if (years < 65) {
    friendlyMessage =
      'Rich in perspective and momentum — every year adds depth to your story.';
  } else {
    friendlyMessage = 'A remarkable legacy of wisdom, resilience, and moments well lived.';
  }

  const yearUnit = years === 1 ? 'year' : 'years';
  const monthUnit = months === 1 ? 'month' : 'months';
  const dayUnit = days === 1 ? 'day' : 'days';
  const totalDaysLabel = totalDays === 1 ? 'day lived' : 'days lived';

  const birthLocalForFormat = new Date(
    Date.UTC(parsedBirth.year, parsedBirth.month - 1, parsedBirth.day)
  );
  const birthDisplay = birthLocalForFormat.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    timeZone: 'UTC',
  });
  const birthWeekday = birthLocalForFormat.toLocaleDateString('en-US', {
    weekday: 'long',
    timeZone: 'UTC',
  });

  return {
    years,
    months,
    days,
    totalDays,
    birthDisplay,
    birthWeekday,
    friendlyMessage,
    totalDaysLabel,
    summaryText: `Your age is ${years} ${yearUnit}, ${months} ${monthUnit}, and ${days} ${dayUnit} old.`,
  };
}

function useAnimatedNumber(target: number | null, triggerKey: number): number {
  const [displayValue, setDisplayValue] = useState<number>(target ?? 0);

  useEffect(() => {
    if (target === null) {
      setDisplayValue(0);
      return;
    }

    const prefersReducedMotion =
      typeof window !== 'undefined' &&
      window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || target <= 0) {
      setDisplayValue(target);
      return;
    }

    let frameId = 0;
    let startTime: number | null = null;
    const duration = 550;

    const step = (timestamp: number) => {
      if (startTime === null) {
        startTime = timestamp;
      }
      const elapsed = timestamp - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      setDisplayValue(Math.round(target * eased));

      if (progress < 1) {
        frameId = window.requestAnimationFrame(step);
      } else {
        setDisplayValue(target);
      }
    };

    frameId = window.requestAnimationFrame(step);
    return () => window.cancelAnimationFrame(frameId);
  }, [target, triggerKey]);

  return displayValue;
}

export default function App() {
  const dobInputRef = useRef<HTMLInputElement | null>(null);
  const [dobInput, setDobInput] = useState<string>('');
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [result, setResult] = useState<AgeResult | null>(null);
  const [calcCount, setCalcCount] = useState<number>(0);

  const today = useMemo(() => new Date(), []);
  const todayIso = useMemo(() => toIsoDate(today), [today]);
  const todayDisplay = useMemo(
    () =>
      today.toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
      }),
    [today]
  );

  const animatedYears = useAnimatedNumber(result ? result.years : null, calcCount);
  const animatedMonths = useAnimatedNumber(result ? result.months : null, calcCount);
  const animatedDays = useAnimatedNumber(result ? result.days : null, calcCount);
  const animatedTotalDays = useAnimatedNumber(result ? result.totalDays : null, calcCount);

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const outcome = calculateExactAge(dobInput, new Date());

    if ('error' in outcome) {
      setErrorMessage(outcome.error);
      setResult(null);
      dobInputRef.current?.focus();
      return;
    }

    setErrorMessage('');
    setResult(outcome);
    setCalcCount((prev) => prev + 1);
  };

  const handleReset = () => {
    setDobInput('');
    setErrorMessage('');
    setResult(null);
    dobInputRef.current?.focus();
  };

  return (
    <div className="relative min-h-screen w-full overflow-x-hidden flex items-center justify-center px-4 py-8 sm:px-6 md:py-12 bg-[radial-gradient(circle_at_18%_18%,rgba(14,165,233,0.16)_0%,transparent_45%),radial-gradient(circle_at_82%_80%,rgba(45,212,191,0.12)_0%,transparent_45%),linear-gradient(145deg,#060a14_0%,#0b1324_52%,#0f172a_100%)]">
      {/* Floating ambient background gradients */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none z-0" aria-hidden="true">
        <div className="orb-one absolute -top-[10%] -left-[8%] w-[clamp(260px,34vw,480px)] h-[clamp(260px,34vw,480px)] rounded-full blur-[72px] opacity-50 bg-[radial-gradient(circle,rgba(56,189,248,0.36),rgba(14,165,233,0)_70%)]" />
        <div className="orb-two absolute -bottom-[12%] -right-[6%] w-[clamp(240px,30vw,440px)] h-[clamp(240px,30vw,440px)] rounded-full blur-[72px] opacity-50 bg-[radial-gradient(circle,rgba(45,212,191,0.28),rgba(45,212,191,0)_70%)]" />
        <div className="orb-three absolute top-[48%] right-[22%] w-[clamp(180px,22vw,320px)] h-[clamp(180px,22vw,320px)] rounded-full blur-[72px] opacity-50 bg-[radial-gradient(circle,rgba(59,130,246,0.22),rgba(59,130,246,0)_70%)]" />
      </div>

      <main className="relative z-10 w-full max-w-[720px] page-entrance">
        <section
          aria-labelledby="page-heading"
          className="rounded-[24px] bg-slate-900/65 backdrop-blur-2xl border border-white/10 p-6 sm:p-10 shadow-[0_28px_70px_-18px_rgba(2,6,23,0.78),inset_0_1px_0_rgba(255,255,255,0.07)]"
        >
          <header className="mb-8">
            <div className="flex flex-wrap items-center gap-2 text-[13px] text-slate-400 mb-4">
              <span className="inline-flex items-center gap-1.5">
                <Calendar className="w-3.5 h-3.5 text-sky-400 shrink-0" aria-hidden="true" />
                <span>Today:</span>
                <time dateTime={todayIso} className="text-slate-300 font-medium">
                  {todayDisplay}
                </time>
              </span>
            </div>

            <h1
              id="page-heading"
              className="text-[clamp(2rem,4.5vw,2.85rem)] font-bold leading-[1.12] tracking-[-0.025em] text-slate-50 mb-3 [text-wrap:balance]"
            >
              Discover Your Age
            </h1>
            <p className="text-[clamp(0.95rem,1.8vw,1.0625rem)] text-slate-300 leading-relaxed max-w-[54ch]">
              Every moment tells a story. Discover exactly how long your journey has been.
            </p>
          </header>

          <form onSubmit={handleSubmit} className="flex flex-col gap-5" noValidate>
            <div className="flex flex-col gap-2">
              <label
                htmlFor="dob"
                className="inline-flex items-center gap-2 text-sm font-semibold text-slate-300"
              >
                <Clock className="w-4 h-4 text-sky-400" aria-hidden="true" />
                <span>Enter your date of birth</span>
              </label>

              <input
                ref={dobInputRef}
                type="date"
                id="dob"
                name="dob"
                max={todayIso}
                value={dobInput}
                onChange={(e) => {
                  setDobInput(e.target.value);
                  if (errorMessage) {
                    setErrorMessage('');
                  }
                }}
                aria-describedby="dob-hint"
                className="date-dark-input font-mono-tabular w-full min-h-[52px] px-4 py-3 text-base font-medium text-slate-50 bg-[#060a14]/60 border border-white/15 rounded-xl transition-all duration-200 hover:border-white/25 hover:bg-[#060a14]/75 focus:outline-none focus:border-sky-400 focus:bg-[#060a14]/85 focus:ring-4 focus:ring-sky-400/15 focus:-translate-y-0.5"
              />

              <p id="dob-hint" className="text-xs text-slate-400">
                Select any date up to {todayDisplay}.
              </p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3.5 mt-1">
              <button
                type="submit"
                className="inline-flex items-center justify-center gap-2 min-h-[48px] px-6 py-3 rounded-xl font-semibold text-[0.95rem] text-white whitespace-nowrap bg-gradient-to-br from-sky-500 to-sky-600 shadow-[0_10px_24px_-8px_rgba(14,165,233,0.55)] transition-all duration-200 hover:from-sky-400 hover:to-sky-500 hover:-translate-y-0.5 hover:shadow-[0_14px_30px_-8px_rgba(56,189,248,0.62)] active:translate-y-0 active:scale-[0.985] focus-visible:outline-2 focus-visible:outline-sky-400 focus-visible:outline-offset-2 cursor-pointer"
              >
                <Calculator className="w-[18px] h-[18px] shrink-0" aria-hidden="true" />
                <span>Calculate Age</span>
              </button>

              <button
                type="button"
                onClick={handleReset}
                className="inline-flex items-center justify-center gap-2 min-h-[48px] px-6 py-3 rounded-xl font-semibold text-[0.95rem] text-slate-300 whitespace-nowrap bg-white/5 border border-white/15 transition-all duration-200 hover:text-white hover:bg-white/10 hover:border-white/25 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.985] focus-visible:outline-2 focus-visible:outline-sky-400 focus-visible:outline-offset-2 cursor-pointer"
              >
                <RotateCcw className="w-[18px] h-[18px] shrink-0" aria-hidden="true" />
                <span>Reset</span>
              </button>
            </div>
          </form>

          {errorMessage !== '' && (
            <div
              role="alert"
              aria-live="assertive"
              className="result-reveal mt-6 flex items-start gap-3 px-4 py-3.5 rounded-xl bg-red-500/10 border border-red-400/35 text-red-200 text-sm leading-relaxed"
            >
              <AlertCircle className="w-[19px] h-[19px] text-red-400 shrink-0 mt-0.5" aria-hidden="true" />
              <span>{errorMessage}</span>
            </div>
          )}

          {result !== null && (
            <section
              key={calcCount}
              aria-live="polite"
              aria-label="Calculated Age Result"
              className="result-reveal mt-8 pt-7 border-t border-white/10"
            >
              <div className="flex flex-wrap items-center justify-between gap-2 mb-5 text-sm text-slate-400">
                <span className="text-slate-300">
                  Born on{' '}
                  <strong className="font-semibold text-slate-50">
                    {result.birthWeekday}, {result.birthDisplay}
                  </strong>
                </span>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <article className="flex sm:block items-center justify-between rounded-2xl bg-white/[0.045] border border-white/10 px-5 py-4 sm:py-5 sm:px-4 sm:text-center transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/[0.075] hover:border-sky-400/30">
                  <span className="font-mono-tabular block text-3xl sm:text-[clamp(2.15rem,5vw,3.1rem)] font-semibold leading-none tracking-tight text-sky-400 sm:mb-2">
                    {animatedYears}
                  </span>
                  <span className="block text-xs font-semibold tracking-[0.16em] uppercase text-slate-400">
                    YEARS
                  </span>
                </article>

                <article className="flex sm:block items-center justify-between rounded-2xl bg-white/[0.045] border border-white/10 px-5 py-4 sm:py-5 sm:px-4 sm:text-center transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/[0.075] hover:border-sky-400/30">
                  <span className="font-mono-tabular block text-3xl sm:text-[clamp(2.15rem,5vw,3.1rem)] font-semibold leading-none tracking-tight text-sky-300 sm:mb-2">
                    {animatedMonths}
                  </span>
                  <span className="block text-xs font-semibold tracking-[0.16em] uppercase text-slate-400">
                    MONTHS
                  </span>
                </article>

                <article className="flex sm:block items-center justify-between rounded-2xl bg-white/[0.045] border border-white/10 px-5 py-4 sm:py-5 sm:px-4 sm:text-center transition-all duration-200 hover:-translate-y-0.5 hover:bg-white/[0.075] hover:border-sky-400/30">
                  <span className="font-mono-tabular block text-3xl sm:text-[clamp(2.15rem,5vw,3.1rem)] font-semibold leading-none tracking-tight text-teal-400 sm:mb-2">
                    {animatedDays}
                  </span>
                  <span className="block text-xs font-semibold tracking-[0.16em] uppercase text-slate-400">
                    DAYS
                  </span>
                </article>
              </div>

              <div className="mt-5 p-4 sm:p-5 rounded-2xl bg-sky-500/[0.08] border border-sky-400/20 flex flex-col gap-2.5">
                <div className="flex flex-wrap items-baseline justify-between gap-2">
                  <p className="text-[0.95rem] font-medium text-slate-50">
                    {result.summaryText}
                  </p>
                  <span className="font-mono-tabular text-[0.95rem] font-semibold text-teal-400">
                    {animatedTotalDays.toLocaleString()} {result.totalDaysLabel}
                  </span>
                </div>

                <p className="flex items-start gap-2 text-sm text-slate-300 leading-relaxed">
                  <Sparkles className="w-4 h-4 text-sky-400 shrink-0 mt-0.5" aria-hidden="true" />
                  <span>{result.friendlyMessage}</span>
                </p>
              </div>
            </section>
          )}
        </section>
      </main>
    </div>
  );
}
