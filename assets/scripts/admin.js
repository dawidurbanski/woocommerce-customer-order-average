(function($) {
  $(document).ready(function() {
    var formTable = $('#woocommerce_customers');

    formTable.find('th.column-spent').after(
      '<th scope="col" class="manage-column column-average">' + l10n.column_title + '</th>'
    );

    formTable.find('td.column-spent').after(
      '<td class="average column-average" data-colname="' + l10n.column_cell_title + '"></td>'
    );

    formTable.find('tbody > tr').each(function() {
      $(this).find('.hidden.orders-average').contents().appendTo($(this).find('.column-average'));
    });
  });
})(jQuery);
