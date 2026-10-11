{{--
    Shared styles for the split-screen auth pages (login, forgot password,
    reset password). Scoped under .lf-* so no other page is affected.
--}}
<style>
    .lf-auth {
        padding: 3.5rem 0 4rem;
    }

    .lf-auth__shell {
        max-width: 1000px;
        margin: 0 auto;
    }

    .lf-auth__card {
        display: grid;
        grid-template-columns: 0.92fr 1.08fr;
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(18, 32, 46, 0.14);
        border: 1px solid rgba(18, 32, 46, 0.06);
    }

    /* ---------- Left brand panel ---------- */
    .lf-panel {
        position: relative;
        padding: 3rem 2.5rem;
        background:
            radial-gradient(120% 90% at 15% 0%, rgba(242, 163, 58, 0.22) 0%, transparent 55%),
            radial-gradient(100% 80% at 100% 100%, rgba(63, 107, 80, 0.28) 0%, transparent 60%),
            linear-gradient(160deg, #12202E 0%, #1b3049 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .lf-panel::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px);
        background-size: 22px 22px;
        opacity: 0.5;
        pointer-events: none;
    }

    .lf-panel > * { position: relative; z-index: 1; }

    .lf-brand {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        text-decoration: none;
        color: #fff;
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 1.15rem;
        letter-spacing: -0.01em;
    }

    .lf-brand:hover { color: #fff; }

    .lf-brand__icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--saffron);
        color: var(--ink);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        box-shadow: 0 4px 12px rgba(242, 163, 58, 0.45);
    }

    .lf-panel__headline {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 1.55rem;
        line-height: 1.25;
        letter-spacing: -0.02em;
        color: #fff;
        margin: 2rem 0 0.75rem;
    }

    .lf-panel__headline span { color: var(--saffron); }

    .lf-panel__text {
        color: rgba(255, 255, 255, 0.72);
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0;
        max-width: 320px;
    }

    .lf-panel__list {
        list-style: none;
        padding: 0;
        margin: 1.75rem 0 0;
        display: flex;
        flex-direction: column;
        gap: 0.7rem;
    }

    .lf-panel__list li {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        color: rgba(255, 255, 255, 0.9);
        font-size: 0.85rem;
        font-weight: 500;
    }

    .lf-panel__list i {
        color: var(--saffron);
        font-size: 0.95rem;
    }

    .lf-panel__foot {
        margin-top: 2.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .lf-panel__foot i { color: var(--sage); }

    /* ---------- Right form panel ---------- */
    .lf-form {
        padding: 3rem 2.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .lf-form__eyebrow {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--saffron-dark);
        margin: 0 0 0.5rem;
    }

    .lf-form__title {
        font-family: 'Fraunces', Georgia, serif;
        font-weight: 700;
        font-size: 1.7rem;
        line-height: 1.15;
        letter-spacing: -0.02em;
        color: var(--ink);
        margin: 0 0 0.4rem;
    }

    .lf-form__subtitle {
        color: var(--ink-soft);
        font-size: 0.9rem;
        line-height: 1.5;
        margin: 0 0 1.75rem;
    }

    .lf-form .form-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: var(--ink);
        margin-bottom: 0.35rem;
    }

    .lf-field {
        display: flex;
        align-items: stretch;
        border: 1.5px solid rgba(18, 32, 46, 0.12);
        border-radius: 11px;
        background: var(--paper);
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        overflow: hidden;
    }

    .lf-field:focus-within {
        border-color: var(--saffron);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(242, 163, 58, 0.15);
    }

    .lf-field__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        color: var(--ink-soft);
        font-size: 1rem;
        flex-shrink: 0;
    }

    .lf-field__input {
        flex-grow: 1;
        min-width: 0;
        border: none;
        background: transparent;
        padding: 0.8rem 0.9rem 0.8rem 0;
        font-size: 0.92rem;
        color: var(--ink);
        outline: none;
    }

    .lf-field__input::placeholder { color: #9aa4af; }

    .lf-field.is-invalid {
        border-color: var(--overdue);
        background: #fdf6f5;
    }

    .lf-field.is-invalid:focus-within {
        box-shadow: 0 0 0 4px rgba(179, 55, 47, 0.14);
    }

    .lf-field__toggle {
        border: none;
        background: transparent;
        color: var(--ink-soft);
        padding: 0 0.9rem;
        font-size: 0.95rem;
        cursor: pointer;
    }

    .lf-field__toggle:hover { color: var(--ink); }

    .lf-error {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        color: var(--overdue);
        font-size: 0.78rem;
        font-weight: 500;
        margin-top: 0.4rem;
    }

    .lf-forgot {
        color: var(--saffron-dark);
        font-weight: 600;
        font-size: 0.8rem;
        text-decoration: none;
    }

    .lf-forgot:hover { color: var(--ink); text-decoration: underline; text-underline-offset: 3px; }

    .lf-remember {
        font-size: 0.85rem;
        color: var(--ink-soft);
    }

    .lf-submit {
        width: 100%;
        justify-content: center;
        border-radius: 11px;
        padding: 0.85rem 1.5rem;
        font-size: 0.95rem;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .lf-divider {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        color: #9aa4af;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin: 1.5rem 0;
    }

    .lf-divider::before,
    .lf-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(18, 32, 46, 0.1);
    }

    .lf-register {
        text-align: center;
        font-size: 0.88rem;
        color: var(--ink-soft);
        margin: 0;
    }

    .lf-register a {
        color: var(--saffron-dark);
        font-weight: 700;
        text-decoration: none;
    }

    .lf-register a:hover { color: var(--ink); text-decoration: underline; text-underline-offset: 3px; }

    .lf-trust {
        text-align: center;
        font-size: 0.78rem;
        color: var(--ink-soft);
        margin: 1.5rem 0 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .lf-trust i { color: var(--sage); }

    /* ---------- Responsive ---------- */
    @media (max-width: 991.98px) {
        .lf-auth__card { grid-template-columns: 1fr; }
        .lf-panel { padding: 2rem; }
        .lf-panel__headline { margin-top: 1.25rem; }
        .lf-panel__list,
        .lf-panel__foot { display: none; }
        .lf-form { padding: 2.25rem 1.75rem; }
    }

    @media (max-width: 575.98px) {
        .lf-auth { padding: 2rem 0 2.5rem; }
        .lf-auth__card { border-radius: 16px; }
        .lf-form { padding: 1.75rem 1.25rem; }
        .lf-form__title { font-size: 1.45rem; }
    }
</style>
