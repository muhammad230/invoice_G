{{--
    "Mark as paid" modal — records a Payment against $invoice and settles it.

    Expects:
      $invoice  App\Models\Invoice
      $methods  array of [value => label] from App\Models\Payment::methods()
--}}
<div class="modal fade" id="markPaidModal" tabindex="-1"
     aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: var(--radius); border: none;">
            <form method="POST" action="{{ route('invoices.pay', $invoice) }}">
                @csrf

                <div class="modal-header border-0">
                    <h5 class="modal-title" id="markPaidModalLabel">
                        <i class="bi bi-check-circle me-2" style="color: var(--sage);"></i>
                        Mark invoice as paid
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body pt-0">
                    <p class="ink-soft mb-4">
                        Recording a full payment of
                        <strong class="ink">{{ $invoice->total_formatted }}</strong>
                        against invoice <strong class="ink">{{ $invoice->invoice_number }}</strong>.
                    </p>

                    <div class="mb-3">
                        <label for="paid_on" class="form-label">Payment date</label>
                        <input type="date" id="paid_on" name="paid_on"
                               class="form-control @error('paid_on') is-invalid @enderror"
                               value="{{ old('paid_on', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}" required>
                        @error('paid_on')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="method" class="form-label">Payment method</label>
                        <select id="method" name="method"
                                class="form-select @error('method') is-invalid @enderror" required>
                            @foreach ($methods as $value => $label)
                                <option value="{{ $value }}" @selected(old('method') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="reference" class="form-label">
                            Reference <span class="ink-soft">(optional)</span>
                        </label>
                        <input type="text" id="reference" name="reference"
                               class="form-control @error('reference') is-invalid @enderror"
                               value="{{ old('reference') }}"
                               maxlength="100"
                               placeholder="Transaction ID, cheque no., note…">
                        @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-ink" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-ink">
                        <i class="bi bi-check-circle me-1"></i> Confirm payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('markPaidModal');
            if (el && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        });
    </script>
@endif
