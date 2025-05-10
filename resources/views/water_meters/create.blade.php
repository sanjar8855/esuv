@extends('layouts.app')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <h1>Yangi Hisoblagich Qo‘shish</h1>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('water_meters.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="customer_id">Mijoz tanlang:</label>
                            <select name="customer_id" id="customerSelect" class="form-control" required>
                                <option></option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="meter_number">Hisoblagich Raqami:</label>
                            <input type="text" name="meter_number" class="form-control" required>
                        </div>

{{--                        <div class="mb-3">--}}
{{--                            <label for="installation_date">O‘rnatilgan Sana:</label>--}}
{{--                            <input type="date" name="installation_date" class="form-control">--}}
{{--                        </div>--}}
                        <div class="mb-3">
                            <label class="form-label">O‘rnatilgan Sana:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input name="installation_date" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend1"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend1'),
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2000,  // Boshlang‘ich yil
                                            maxYear: new Date().getFullYear(),  // Hozirgi yildan keyingi 10 yilgacha
                                            months: true,  // Oynilar dropdownda chiqishi uchun
                                            years: true  // Yillarni dropdown shaklida chiqarish
                                        },
                                        buttonText: {
                                            previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                            nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                        },
                                    }));
                                });
                            </script>
                        </div>

                        <div class="mb-3">
                            <label for="validity_period">Amal qilish muddati (yillarda):</label>
                            <input type="number" name="validity_period" id="validity_period" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tugash muddati sanasi:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input name="expiration_date" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend3"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend3'),
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2000,  // Boshlang‘ich yil
                                            maxYear: new Date().getFullYear()+10,  // Hozirgi yildan keyingi 10 yilgacha
                                            months: true,  // Oynilar dropdownda chiqishi uchun
                                            years: true  // Yillarni dropdown shaklida chiqarish
                                        },
                                        buttonText: {
                                            previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                            nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                        },
                                    }));
                                });
                            </script>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Oxirgi tekshirilgan sana:</label>

                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <!-- Download SVG icon from http://tabler.io/icons/icon/calendar -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                                </span>
                                <input name="last_reading_date" class="form-control" placeholder="Sanani tanlang" id="datepicker-icon-prepend2"/>
                            </div>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    window.Litepicker && (new Litepicker({
                                        element: document.getElementById('datepicker-icon-prepend2'),
                                        format: 'YYYY-MM-DD',
                                        dropdowns: {
                                            minYear: 2000,  // Boshlang‘ich yil
                                            maxYear: new Date().getFullYear(),  // Hozirgi yildan keyingi 10 yilgacha
                                            months: true,  // Oynilar dropdownda chiqishi uchun
                                            years: true  // Yillarni dropdown shaklida chiqarish
                                        },
                                        buttonText: {
                                            previousMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-left -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M15 6l-6 6l6 6" /></svg>`,
                                            nextMonth: `<!-- Download SVG icon from http://tabler.io/icons/icon/chevron-right -->
	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1"><path d="M9 6l6 6l-6 6" /></svg>`,
                                        },
                                    }));
                                });
                            </script>
                        </div>

                        <button type="submit" class="btn btn-primary">Saqlash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('tabler/libs/litepicker/dist/litepicker.js') }}" defer></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let installationDateInput = document.getElementById("datepicker-icon-prepend1");
            let validityPeriodInput = document.getElementById("validity_period");
            let expirationDateInput = document.getElementById("datepicker-icon-prepend3");

            function calculateExpirationDate() {
                let installationDate = installationDateInput.value;
                let validityPeriod = parseInt(validityPeriodInput.value, 10);

                if (installationDate && !isNaN(validityPeriod)) {
                    let installDateObj = new Date(installationDate);
                    installDateObj.setFullYear(installDateObj.getFullYear() + validityPeriod);

                    let formattedDate = installDateObj.toISOString().split("T")[0]; // YYYY-MM-DD format
                    expirationDateInput.value = formattedDate;
                }
            }

            // Hodisalarni tinglash
            installationDateInput.addEventListener("change", calculateExpirationDate);
            validityPeriodInput.addEventListener("input", calculateExpirationDate);
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            new TomSelect("#customerSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Mijozni tanlang...",
                allowEmptyOption: true
            });
        });
    </script>
@endsection
