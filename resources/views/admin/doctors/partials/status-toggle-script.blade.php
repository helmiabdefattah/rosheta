@push('scripts')
<script>
(function () {
    const isAr = @json(app()->getLocale() === 'ar');
    const activeLabel = isAr ? 'مفعّل' : 'Active';
    const inactiveLabel = isAr ? 'غير مفعّل' : 'Inactive';
    const errorMsg = isAr ? 'تعذر تحديث الحالة. حاول مرة أخرى.' : 'Could not update status. Please try again.';

    $(document).on('change', '.doctor-active-toggle-input', function () {
        const $input = $(this);
        const $wrap = $input.closest('.doctor-active-toggle');
        const $label = $wrap.find('.doctor-active-toggle-label');
        const previousChecked = !$input.is(':checked');

        $input.prop('disabled', true);

        $.ajax({
            url: $wrap.data('toggle-url'),
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                Accept: 'application/json',
            },
        })
            .done(function (res) {
                if (!res.success) {
                    $input.prop('checked', previousChecked);
                    toastr.error(res.message || errorMsg);
                    return;
                }

                const on = !!res.is_active;
                $input.prop('checked', on);
                $label
                    .text(on ? activeLabel : inactiveLabel)
                    .toggleClass('text-emerald-700', on)
                    .toggleClass('text-red-600', !on);
                toastr.success(res.message);
            })
            .fail(function (xhr) {
                $input.prop('checked', previousChecked);
                toastr.error(xhr.responseJSON?.message || errorMsg);
            })
            .always(function () {
                $input.prop('disabled', false);
            });
    });
})();
</script>
@endpush
