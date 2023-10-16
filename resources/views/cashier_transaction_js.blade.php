

<script>

    function cashier_transaction_js(){
        $.ajax({
            type: "GET",
            url: "{{ route('cashier_transaction_route') }}",
            dataType: "json",
            success: function(data){
                $("#div_body").html(data.html);
                if(data.html !== ''){
                    get_cashier_transaction_js();
                }
            }
        });
    }

    function get_cashier_transaction_js(){
        var dcode = $("#department option:selected").val();
        var date = $("#sales_date").val();
        
        $.ajax({
            type: 'GET',
            url: '{{ url("get_cashier_transaction_route") }}',
            data: {'dcode': dcode,
                   'date': date
            },
            dataType: 'json',
            success: function(data){
                $("#div_ct_table").html(data.html);
            }
        });
    }

    function cash_den_js(tr_no,emp_id,terminal_no){
        $("#cd_modal_footer").prop('hidden', true);
        const partial = document.getElementById('partial_btn');
        const final = document.getElementById('final_btn');
        partial.classList.remove('active');
        final.classList.remove('active');
        $("#den_info").text(tr_no+'|'+emp_id+'|'+terminal_no);
        get_partial_den_js();
    }

    function get_partial_den_js(){
        $("#cd_modal_footer").prop('hidden', true);
        const partial = document.getElementById('partial_btn');
        const final = document.getElementById('final_btn');
        // Check if the button is currently active
        const isActive = partial.classList.contains('active');
        if (!isActive) {
            partial.classList.toggle('active');
        }
        final.classList.remove('active'); 
        const den_info = $("#den_info").text().split('|');

        $("#cash_den_tbl").html('');
        $.ajax({
            type: 'GET',
            url: '{{ url("get_partial_den_route") }}',
            data: {'tr_no': den_info[0],
                   'emp_id': den_info[1],
                   'terminal_no': den_info[2]
            },
            dataType: 'json',
            success: function(data){
                $("#cname").text('of '+data.emp_name);
                $("#cash_den_tbl").html(data.html);
            }
        });
    }

    function get_final_den_js(){
        $("#cd_modal_footer").prop('hidden', false);
        const partial = document.getElementById('partial_btn');
        const final = document.getElementById('final_btn');
         // Check if the button is currently active
        const isActive = final.classList.contains('active');
        if (!isActive) {
            final.classList.toggle('active');
        }
        partial.classList.remove('active');
        const den_info = $("#den_info").text().split('|');

        $("#cash_den_tbl").html('');
        $.ajax({
            type: "GET",
            url: "{{ url('get_final_den_route') }}",
            data: {'tr_no': den_info[0],
                   'emp_id': den_info[1],
                   'terminal_no': den_info[2]
            },
            dataType: 'json',
            success: function (data){
                $("#cname").text('of '+data.emp_name);
                $("#cash_den_tbl").html(data.html);
            }
        });
    }

    function validate_input_js(event){
        // ==================================Disabled e-+.==============================================
        if (event.key === 'e' || event.key === 'E' || event.key === '+' || event.key === '-' || event.key === '.') {
            event.preventDefault();
        }
    }

    function calculate_ptotal_js(id){
        // ===================================Update the Total==========================================
        const onek = $("#onek"+id).val() * 1000;
        const fiveh = $("#fiveh"+id).val() * 500;
        const twoh = $("#twoh"+id).val() * 200;
        const oneh = $("#oneh"+id).val() * 100;
        const fifty = $("#fifty"+id).val() * 50;
        const twenty = $("#twenty"+id).val() * 20;
        const total = onek + fiveh + twoh + oneh + fifty + twenty;
        $("#ptotal"+id).text(total.toLocaleString());
        // toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); for decimals

    }

    function arrow_updown_js(input,id){
        moveCursorToEndNumber(input);
        // =================Update the Total=======================
        const onek = $("#onek"+id).val() * 1000;
        const fiveh = $("#fiveh"+id).val() * 500;
        const twoh = $("#twoh"+id).val() * 200;
        const oneh = $("#oneh"+id).val() * 100;
        const fifty = $("#fifty"+id).val() * 50;
        const twenty = $("#twenty"+id).val() * 20;
        const total = onek + fiveh + twoh + oneh + fifty + twenty;
        $("#ptotal"+id).text(total.toLocaleString());
    }

    function calculate_ftotal_js(){
        // ===================================Update the Total==========================================
        const onek = $("#onek").val() * 1000;
        const fiveh = $("#fiveh").val() * 500;
        const twoh = $("#twoh").val() * 200;
        const oneh = $("#oneh").val() * 100;
        const fifty = $("#fifty").val() * 50;
        const twenty = $("#twenty").val() * 20;
        const ten = $("#ten").val() * 10;
        const five = $("#five").val() * 5;
        const one = $("#one").val() * 1;
        const twentyfive_cents = $("#twentyfive_cents").val() * 0.25;
        const ten_cents = $("#ten_cents").val() * 0.10;
        const five_cents = $("#five_cents").val() * 0.05;
        const one_cents = $("#one_cents").val() * 0.01;
        const total = onek + fiveh + twoh + oneh + fifty + twenty + ten + five + one + twentyfive_cents + ten_cents + five_cents + one_cents;
        $("#ftotal").text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    }

    function final_arrow_updown_js(input){
        moveCursorToEndNumber(input);
        // =====================Update the Total========================
        const onek = $("#onek").val() * 1000;
        const fiveh = $("#fiveh").val() * 500;
        const twoh = $("#twoh").val() * 200;
        const oneh = $("#oneh").val() * 100;
        const fifty = $("#fifty").val() * 50;
        const twenty = $("#twenty").val() * 20;
        const ten = $("#ten").val() * 10;
        const five = $("#five").val() * 5;
        const one = $("#one").val() * 1;
        const twentyfive_cents = $("#twentyfive_cents").val() * 0.25;
        const ten_cents = $("#ten_cents").val() * 0.10;
        const five_cents = $("#five_cents").val() * 0.05;
        const one_cents = $("#one_cents").val() * 0.01;
        const total = onek + fiveh + twoh + oneh + fifty + twenty + ten + five + one + twentyfive_cents + ten_cents + five_cents + one_cents;
        $("#ftotal").text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    }

    function update_partial_cd_js(counter_id,data_id,tr_no,emp_id,old_total){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
        if (result.isConfirmed) {
            const sales_date = $("#sales_date").val();
            const dcode = $("#department option:selected").val();
            const onek = $("#onek"+counter_id).val();
            const fiveh = $("#fiveh"+counter_id).val();
            const twoh = $("#twoh"+counter_id).val();
            const oneh = $("#oneh"+counter_id).val();
            const fifty = $("#fifty"+counter_id).val();
            const twenty = $("#twenty"+counter_id).val();
            const total = $("#ptotal"+counter_id).text().split(',').join('');
            const variance = total - old_total;
            $.ajax({
                type: 'POST',
                url: '{{ url("update_partial_cd_route") }}',
                data: { _token: '{{ csrf_token() }}',
                    'id': data_id,
                    'tr_no': tr_no, 
                    'emp_id': emp_id, 
                    'sales_date': sales_date, 
                    'dcode': dcode, 
                    'onek': onek, 
                    'fiveh': fiveh,
                    'twoh': twoh,
                    'oneh': oneh,
                    'fifty': fifty,
                    'twenty': twenty,
                    'total': total,
                    'variance': variance
                },
                dataType: 'json',
                success: function(data){
                    Swal.fire('Updated!', 'Cashier Denomination has been updated.', 'success');
                }
            });
        }
        });
    }

    function update_final_cd_js(){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
        if (result.isConfirmed) {
            const sales_date = $("#sales_date").val();
            const dcode = $("#department option:selected").val();
            const den_info = $("#den_info").text().split('|');
            const final_info = $("#final_info").text().split(',');
            const onek = $("#onek").val();
            const fiveh = $("#fiveh").val();
            const twoh = $("#twoh").val();
            const oneh = $("#oneh").val();
            const fifty = $("#fifty").val();
            const twenty = $("#twenty").val();
            const ten = $("#ten").val();
            const five = $("#five").val();
            const one = $("#one").val();
            const twentyfive_cents = $("#twentyfive_cents").val();
            const ten_cents = $("#ten_cents").val();
            const five_cents = $("#five_cents").val();
            const one_cents = $("#one_cents").val();
            const total = $("#ftotal").text().split(',').join('');
            const variance = total - final_info[0];
            $.ajax({
                type: 'POST',
                url: '{{ url("update_final_cd_route") }}',
                data: { _token: '{{ csrf_token() }}',
                    'id': final_info[1],
                    'tr_no': den_info[0], 
                    'emp_id': den_info[1], 
                    'terminal_no': den_info[2], 
                    'sales_date': sales_date, 
                    'dcode': dcode, 
                    'onek': onek, 
                    'fiveh': fiveh,
                    'twoh': twoh,
                    'oneh': oneh,
                    'fifty': fifty,
                    'twenty': twenty,
                    'ten': ten,
                    'five': five,
                    'one': one,
                    'twentyfive_cents': twentyfive_cents,
                    'ten_cents': ten_cents,
                    'five_cents': five_cents,
                    'one_cents': one_cents,
                    'total': total,
                    'variance': variance.toFixed(2)
                },
                dataType: 'json',
                success: function(data){
                    Swal.fire('Updated!', 'Cashier Denomination has been updated.', 'success');
                }
            });
        }
        });
    }

    function noncash_den_js(tr_no,emp_id,terminal_no){
        const sales_date = $("#sales_date").val();
        $("#tnc_mop").html('');
        $("#noncash_den_tbl").html('');
        $.ajax({
            type: 'GET',
            url: '{{  url("get_noncash_den_route") }}',
            data: {'tr_no': tr_no,
                   'emp_id': emp_id,
                   'terminal_no': terminal_no,
                   'sales_date': sales_date
            },
            dataType: 'json',
            success: function(data) {
                $("#ncname").text('of '+data.emp_name);
                $("#ncden_info").text(tr_no+'|'+emp_id+'|'+terminal_no+'|'+sales_date);
                $("#tnc_mop").html(data.mop_array_html);
                $("#noncash_den_tbl").html(data.html);
            }
        });
    }

    function formatCurrency(input) {
        // Remove non-numeric characters
        let value = input.value.replace(/[^\d.]/g, "");
        // Check if the input is negative
        let isNegative = false;
        if (value.startsWith("-")) {
            isNegative = true;
            value = value.substring(1);
        }
        // Split into whole number and decimal parts
        let parts = value.split(".");
        let whole = parts[0];
        let decimal = parts.length > 1 ? "." + parts[1].slice(0, 2) : "";
        // Add commas for thousands separator
        whole = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        // Concatenate the whole number and decimal parts
        let formattedValue = isNegative ? "-" + whole + decimal : whole + decimal;
        // Update the input value
        input.value = formattedValue;
    }

    function moveCursorToEndText(input) {
        let length = input.value.length;
        input.setSelectionRange(length, length);
    }

    function moveCursorToEndNumber(input) {
        // Get the current value of the input
        let value = input.value;
        // Set the value of the input to an empty string
        input.value = '';
        // Set the value of the input to the original value to move the cursor to the end
        input.value = value;
    }

    function update_noncash_js(id,old_amount){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var cashier_info = $("#ncden_info").text().split('|');
                var dcode = $("#department option:selected").val();
                var mop_name = $("#mop_name"+id+" option:selected").text();
                var noncash_qty = $("#noncash_qty"+id).val();
                var new_noncash_amount = $("#noncash_amount"+id).val().split(',').join('');
                var variance = new_noncash_amount - old_amount;
                // =========================================================================
                $.ajax({
                    type: 'POST',
                    url: '{{ url("update_noncash_route") }}',
                    data: {_token: '{{ csrf_token() }}',
                            'id': id,
                            'tr_no': cashier_info[0],
                            'emp_id': cashier_info[1],
                            'terminal_no': cashier_info[2],
                            'sales_date': cashier_info[3],
                            'dcode': dcode,
                            'mop_name': mop_name,
                            'noncash_qty': noncash_qty,
                            'noncash_amount': new_noncash_amount,
                            'variance': variance.toFixed(2)
                    },
                    dataType: 'json',
                    success: function(data){
                        Swal.fire('UPDATED', '', 'success');
                        noncash_den_js(cashier_info[0],cashier_info[1],cashier_info[2]);
                    }
                });
            }
        });
    }

    function transfer_noncash_js(id,mop_array,mop_name,qty,amount){
        $("#tnc_qty").val('');
        $("#tnc_amount").val('');
        $("#tncden_info").text(id+'|'+qty+'|'+amount);
        $("#tncname").text('From '+mop_name+': ('+qty+' - '+amount.toLocaleString()+')');
    }

    function transfer_mop_js(){
        var cashier_info = $("#ncden_info").text().split('|');
        var nc_info =  $("#tncden_info").text().split('|');
        var mop = $("#tnc_mop option:selected").val();
        var qty = $("#tnc_qty").val();
        var amount = $("#tnc_amount").val().split(',').join('');
        // =====================================================
        if(qty == '' || amount == ''){
            Swal.fire('INVALID', 'Quantity and Amount must not be empty.', 'error');
        }else if(parseInt(qty) > parseInt(nc_info[1])){
            Swal.fire('INVALID', 'Transfer quantity not greater than '+nc_info[1], 'error');
        }else if(parseFloat(amount) > parseFloat(nc_info[2])){
            Swal.fire('INVALID', 'Transfer amount not greater than '+parseFloat(nc_info[2]).toLocaleString(), 'error');
        }else{
            Swal.fire({
            title: 'Confirmation',
            text: "Are you sure you want to transfer?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a673',
            cancelButtonColor: '#2c9faf',
            confirmButtonText: 'Yes, transfer it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    var old_qty = parseInt(nc_info[1]) - parseInt(qty);
                    var old_amount = parseFloat(nc_info[2]) - parseFloat(amount);
                    $.ajax({
                        type: 'POST',
                        url: '{{ url("transfer_mop_route") }}',
                        data: {_token: '{{ csrf_token() }}',
                            'id': nc_info[0],
                            'old_qty': old_qty,
                            'old_amount': old_amount,
                            'new_mop': mop,
                            'new_qty': qty,
                            'new_amount': amount
                        },
                        dataType: 'json',
                        success: function(data){
                            if(data.message == 'updated'){
                                $("#transferNonCashDenModal").hide();
                                Swal.fire('TRANSFERRED', '', 'success');
                                noncash_den_js(cashier_info[0],cashier_info[1],cashier_info[2]);
                            }
                        }
                    });
                }
            });
        }
    }

    function view_terminal_js(tr_no,emp_id,location,sales_date,emp_name,terminal_no){
        $("#terminalModalLabel").text(emp_name);
        $("#terminal_info").text(tr_no+'|'+emp_id+'|'+location+'|'+sales_date+'|'+terminal_no);
        // ========================================
        $("#old_terminal_data").text('');
        $("#registered_sales").val('');
        $("#discount").val('');
        $("#transaction_count").val('');
        $("#terminal_no").html('');
        $.ajax({
            type: "GET",
            url: "{{ url('get_terminal_route') }}",
            data: {'tr_no': tr_no,
                   'emp_id': emp_id,
                   'location': location,
                   'sales_date': sales_date,
                   'terminal_no': terminal_no
            },
            dataType: "json",
            success: function(data){
                $("#old_terminal_data").text(data.old_terminal_data);
                $("#registered_sales").val(data.registered_sales);
                $("#discount").val(data.discount);
                $("#transaction_count").val(data.tr_count);
                $("#terminal_no").html(data.terminal_html);
            }
        });
    }

    function update_terminal_js(){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var terminal_info = $("#terminal_info").text().split('|');
                var old_data = $("#old_terminal_data").text().split('|');
                var new_counter = $("#terminal_no option:selected").val();
                var new_terminal = $("#terminal_no option:selected").text();
                var new_rs = $("#registered_sales").val().split(',').join('');
                var new_discount = $("#discount").val().split(',').join('');
                var new_tc = $("#transaction_count").val();
                
                $.ajax({
                    type: "POST",
                    url: "{{ url('update_terminal_route') }}",
                    data: {_token: '{{ csrf_token() }}',
                            'tr_no': terminal_info[0],
                            'emp_id': terminal_info[1],
                            'location': terminal_info[2],
                            'sales_date': terminal_info[3],
                            'total_sales': old_data[0],
                            'old_rs': old_data[1],
                            'old_discount': old_data[2],
                            'old_tc': old_data[3],
                            'new_terminal': new_terminal,
                            'new_counter': new_counter,
                            'new_rs': new_rs,
                            'new_discount': new_discount,
                            'new_tc': new_tc
                    },
                    dataType: "json",
                    success: function(data){
                        get_cashier_transaction_js();
                        Swal.fire('UPDATED', '', 'success');
                    }
                });
            }
        });
    }

    function view_sales_date_js(tr_no,emp_id,location,sales_date){
        $("#sales_date_info").text(tr_no+'|'+emp_id+'|'+location+'|'+sales_date);
        $("#old_sales_date").text(sales_date);
        $("#new_sales_date").val(sales_date);
    }

    function update_sales_date_js(){
        var info =  $("#sales_date_info").text().split('|');
        var new_date = $("#new_sales_date").val(); 
        if(info[3] == new_date){
            Swal.fire('INVALID DATE', 'Please select another date.', 'error');
        }else{
            Swal.fire({
            title: 'Confirmation',
            text: "Are you sure you want to update?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#17a673',
            cancelButtonColor: '#2c9faf',
            confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '{{ url("update_sales_date_route") }}',
                        data: {_token: '{{ csrf_token() }}',
                               'tr_no': info[0],
                               'emp_id': info[1],
                               'old_date': info[3],
                               'new_date': new_date,
                        },
                        dataType: 'json',
                        success: function(data){
                            get_cashier_transaction_js();
                            Swal.fire('UPDATED', '', 'success');
                        }
                    });
                }
            });
        }
    }

    function view_location_js(tr_no,emp_id,location,sales_date,borrowed){                    
        $("#location_info").text(tr_no+'|'+emp_id+'|'+location+'|'+sales_date+'|'+borrowed);
        // ==================================
        if(borrowed == 'YES'){
        $("#borrowed_yes").prop('selected', true);
        }else if(borrowed == 'NO'){
        $("#borrowed_no").prop('selected', true);
        }
        // ==================================
        $("#old_location").text('');
        $("#locDepartment").html('');
        $("#locSection").html('');
        $("#locSubSection").html('');
        $.ajax({
            type: "GET",
            url: "{{ url('get_location_route') }}",
            data: {'location': location},
            success: function(data){
                $("#old_location").text(data.location_name);
                $("#locDepartment").html(data.dept_name);
                $("#locSection").html(data.section_name);
                $("#locSubSection").html(data.sub_section_name);
            }
        });
    }

    function get_section_js(){
        var dcode = $("#locDepartment option:selected").val();
        // =================================================
        if(dcode != null){
            $.ajax({
                type: 'GET',
                url: '{{ url("get_section_route") }}',
                data: {'dcode': dcode},
                dataType: 'json',
                success: function(data){
                    $("#locSection").html(data.section_html);
                    get_sub_section_js();
                }
            });
        }
    }

    function get_sub_section_js(){
        var scode = $("#locSection option:selected").val();
        // ================================================
        if(scode != null){
            $.ajax({
                type: 'GET',
                url: '{{ url("get_sub_section_route") }}',
                data: {scode: scode},
                dataType: 'json',
                success: function(data){
                    $("#locSubSection").html(data.sub_section_html);
                }
            });
        }else{
            $("#locSubSection").html('');
        }
    }

    function update_location_js(){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var cashier_info = $("#location_info").text().split('|');
                var dcode = $("#locDepartment option:selected").val();
                var scode = $("#locSection option:selected").val();
                var sscode = $("#locSubSection option:selected").val();
                var borrowed = $("#locBorrowed option:selected").text();
                // ========================================================
                var location = '';
                if(sscode != null){
                location = sscode;
                }else if(scode != null){
                location = scode;
                }else if(dcode != null){
                location = dcode;
                }
                // ========================================================
                if(location == cashier_info[2] && borrowed == cashier_info[4]){
                    Swal.fire('INVALID LOCATION!', 'Please select another location.', 'error');
                }else{
                    $.ajax({
                        type: 'POST',
                        url: '{{ url("update_location_route") }}',
                        data: {_token: '{{ csrf_token() }}',
                               'tr_no': cashier_info[0], 
                               'emp_id': cashier_info[1],
                               'sales_date': cashier_info[3],
                               'location': location,
                               'borrowed': borrowed
                        },
                        dataType: 'json',
                        success: function(data){
                        if(data.message == 'invalid'){
                            Swal.fire('INVALID LOCATION!', 'You cannot borrow your current location.', 'error');
                        }else{
                            view_location_js(cashier_info[0],cashier_info[1],location,cashier_info[3],borrowed);
                            get_cashier_transaction_js();
                            Swal.fire('UPDATED', '', 'success');
                        }
                        }
                    });
                }
            }
        });
    }

    function view_batch_remittance_js(tr_no,emp_id,location,sales_date,emp_name,terminal_no){
        $("#batch_remittance_info").text(tr_no+'|'+emp_id+'|'+location+'|'+sales_date+'|'+emp_name+'|'+terminal_no);
        $("#batch_name").text(emp_name);
        // ===============================
        $("#batch_remittance_tbl").html('');
        $.ajax({
            type: "GET",
            url: "{{ url('get_batch_remittance_route') }}",
            data: {'tr_no': tr_no,
                   'emp_id': emp_id,
                   'terminal_no': terminal_no,
                   'sales_date': sales_date
            },
            dataType: "json",
            success: function(data){
                $("#batch_remittance_tbl").html(data.html);
            }
        });
    }

    function update_batch_remittance_js(id,batch){
        Swal.fire({
        title: 'Confirmation',
        text: "Are you sure you want to update?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#17a673',
        cancelButtonColor: '#2c9faf',
        confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                var batch_info = $("#batch_remittance_info").text().split('|');
                var new_batch = $("#batch_no"+id).val();
                var new_date = $("#batch_date"+id).val();
                if(new_batch == batch && new_date == batch_info[3]){
                    Swal.fire('INVALID', 'Please change the batch or date before you update.', 'error');
                }else if(new_batch < 1 || new_batch == ''){
                    Swal.fire('INVALID BATCH', 'Batch not less than 1 or empty.', 'error');
                }else{
                    $.ajax({
                        type: "POST",
                        url: "{{ url('update_batch_remittance_route') }}",
                        data: {_token: '{{ csrf_token() }}',
                               'id': id,
                               'new_batch': new_batch,
                               'new_date': new_date,
                               'dcode': batch_info[2]
                        },
                        dataType: "json",
                        success: function(data){
                            view_batch_remittance_js(batch_info[0],batch_info[1],batch_info[2],batch_info[3],batch_info[4],batch_info[5]);
                            get_cashier_transaction_js();
                            Swal.fire('UPDATED', '', 'success');
                        }
                    });
                }
            }
        });
    }




</script>