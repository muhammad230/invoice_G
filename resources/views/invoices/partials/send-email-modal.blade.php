{{--
    "Send invoice" / "Send reminder" modal.

    One modal instance is rendered per (invoice, type) pair. Each form posts
    to the same endpoint; a hidden `type` field tells the controller whether
    this is an invoice copy or a payment reminder.

    Expects:
      $invoice  App\Models\Invoice
      $type     string  'invoice' | 'reminder'

    When validation fails the request redirects back with errors + old input.
    Only the modal whose invoice_id + type match the failed submission is
    re-opened and repopulated, so other rows on the list page stay untouched.
--}}
@php
    $isReminder = $type === 'reminder';
    $modalId    = 'sendEmailModal-' . $type . '-' . $invoice->id;

    // Business name fallback: invoice snapshot → profile → user → app name.
    $businessName = $invoice->business_name
        ?: optional(optional($invoice->user)->businessProfile)->business_name
        ?: optional($invoice->user)->name
        ?: (auth()->user()->name ?? 'Your business');

    $recipient  = optional($invoice->client)->email ?? '';
    $amount     = $invoice->total_formatted;
    $dueDate    = $invoice->due_date ? $invoice->due_date->format('M j, Y') : null;
    $clientName = optional($invoice->client)->name ?? 'there';
    $firstName  = trim(explode(' ', trim($clientName))[0] ?? '') ?: 'there';
    $duePhrase  = $dueDate ? 'due on ' . $dueDate : 'due on receipt';

    $defaultSubject = $isReminder
        ? 'Reminder: Invoice ' . $invoice->invoice_number . ' is due'
        : 'Invoice ' . $invoice->invoice_number . ' from ' . $businessName;

    $defaultMessage = $isReminder
        ? "Hi {$firstName},\n\nThis is a friendly reminder that invoice {$invoice->invoice_number} for {$amount} is {$duePhrase}.\n\nIf you have already sent payment, please disregard this message. Otherwise we would appreciate payment at your earliest convenience.\n\nThank you,\n{$businessName}"
        : "Hi {$firstName},\n\nPlease find attached invoice {$invoice->invoice_number} for {$amount}, {$duePhrase}.\n\nThank you for your business. If you have any questions, simply reply to this email.\n\nBest regards,\n{$businessName}";

    // Was this the modal that failed validation?
    $isErrored = (string) old('invoice_id') === (string) $invoice->id
        && old('type') === $type;

    // Repopulate from old input only for the failed modal.
    $val = fn ($field, $default) => $isErrored ? old($field, $default) : $default;
    $hasError = fn ($field) => $isErrored && $errors->has($field);
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <form method="POST" action="{{ route('invoices.send', $invoice) }}">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="modal-header border-0">
                    <h5 class="modal-title" id="{{ $modalId }}-label">
                        <i class="bi {{ $isReminder ? 'bi-bell' : 'bi-envelope-paper' }} me-2" style="color: var(--saffron-dark);"></i>
                        {{ $isReminder ? 'Send a payment reminder' : 'Send invoice' }}
                        <span class="ink-soft fw-normal">— {{ $invoice->invoice_number }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <div class="mb-3">
                        <label for="{{ $modalId }}-to" class="form-label">To</label>
                        <input type="email"
                               id="{{ $modalId }}-to"
                               name="to"
                               class="form-control {{ $hasError('to') ? 'is-invalid' : '' }}"
                               value="{{ $val('to', $recipient) }}"
                               maxlength="150"
                               required
                               autocomplete="email"
                               placeholder="client@example.com">
                        @if ($hasError('to'))
                            <div class="invalid-feedback">{{ $errors->first('to') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="{{ $modalId }}-subject" class="form-label">Subject</label>
                        <input type="text"
                               id="{{ $modalId }}-subject"
                               name="subject"
                               class="form-control {{ $hasError('subject') ? 'is-invalid' : '' }}"
                               value="{{ $val('subject', $defaultSubject) }}"
                               maxlength="150"
                               required>
                        @if ($hasError('subject'))
                            <div class="invalid-feedback">{{ $errors->first('subject') }}</div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="{{ $modalId }}-message" class="form-label">Message</label>
                        <textarea id="{{ $modalId }}-message"
                                  name="message"
                                  rows="7"
                                  class="form-control {{ $hasError('message') ? 'is-invalid' : '' }}"
                                  maxlength="2000"
                                  required>{{ $val('message', $defaultMessage) }}</textarea>
                        @if ($hasError('message'))
                            <div class="invalid-feedback">{{ $errors->first('message') }}</div>
                        @endif
                        <div class="form-text">A PDF copy of the invoice is attached automatically.</div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="checkbox"
                               value="1"
                               id="{{ $modalId }}-copy"
                               name="send_copy"
                               @checked($isErrored ? (bool) old('send_copy') : false)>
                        <label class="form-check-label" for="{{ $modalId }}-copy">
                            Send me a copy
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-saffron">
                        <i class="bi bi-send me-1"></i>
                        {{ $isReminder ? 'Send reminder' : 'Send invoice' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($isErrored)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById(@json($modalId));
            if (el && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        });
    </script>
@endif
