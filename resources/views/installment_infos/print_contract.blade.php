<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="/print.css">
</head>

<body>

    <button onclick="printDiv()" id="myBtn" title="Печать">Печать</button>

    <div class="a4" id="printarea">
        <div class="text-center">
            <h3>Кесте</h3>
        </div>

        <div class="text-justify">
            <p>
                <b>{{ $shopOrder->customer->fullname }}</b> №<b>{{ $shopOrder->id }} </b> шәртнама тийкарында «Муратбек
                мебель» ЖШЖнен улума бахасы <b>{{ nf($shopOrder->total) }}</b> болған мебель
                <b>{{ $shopOrder->installment_payments->count() - 1 }}</b>-айға бөлип төлеуге алып атырман. Басланғыш
                төлем есабында <b>{{ nf($shopOrder->installment_payments->first()->summa) }}</b>
                (сўм)(<b>{{ $shopOrder->installment_start_percent }}</b>%) төленеди. Гиреу есабында:
            </p>
            <p>
                1) Дәрәмат мағлыўматнамасы
                <br>
                2) Кепил, кепилдиң дәрәмат мағлыўматнамасы
            </p>
            <p>
                Егер қалған сумманы өз уақтында төленбесе устине пайыз косылыуына разыман. Жәнеде 1ай даўамында төлемди
                төлей алмасам мебеллерди қайтарып алыуға разыман ҳәм басланғыш төлемди талап етпеймен.
            </p>
            <p>
                Кесте менен таныстым _____________(қолы)
            </p>
            <p>
                Ф.И.О: <b>{{ $shopOrder->customer->fullname }}</b><br>
                Адрес:
                <b>{{ $shopOrder->customer->address ?? '____________________________________________________' }}</b><br>
                Жумыс орны:
                <b>{{ $shopOrder->customer->work ?? '____________________________________________________' }}</b><br>
                Телефон: <b>+998{{ $shopOrder->customer->phone }}</b><br>
            </p>
            <div class="text-center">
                <h3>Еслетпе: <br>Басланғыш төлем 40%дан төмен болмаўы керек!</h3>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Ай (Месяц)</th>
                        <th>Хәр айда толенетуғын сумма</th>
                        <th>Қалған сумма</th>
                        <th>Қолы (подпись)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $debt_summa = $shopOrder->total; ?>
                    @foreach ($shopOrder->installment_payments as $installment_payment)
                        <?php $debt_summa -= $installment_payment->summa; ?>
                        <tr class="text-center">
                            <td>{{ dfd($installment_payment->date) }}</td>
                            <td>{{ nf($installment_payment->summa) }}</td>
                            <td>{{ nf($debt_summa) }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="break-after:page"></div>

            <div class="text-center">
                <h3>№{{ $shopOrder->id }}-санлы ҚАРЫЗ ШӘТРНАМАСЫ</h3>
            </div>

            <p>
                Нөкис қаласы {{ dfd(date('d-m-Y')) }} жыл «MURATBEK MEBEL» ЖШЖ (кейинги орынларда карызға бериуши)
                атынан устав тийкарында М.Танирбергенов бир тәрептен хәм <b>{{ $shopOrder->customer->fullname }}</b>
                (кейинги орынларда карызға алыушы деп жүритиледи) екинши тәрептен төмендегилер хаққында бул шәртнаманы
                дұзди.
            </p>

            <div class="text-center">
                <h3>1.ШӘРТНАМА ПРЕДМЕТИ</h3>
            </div>

            <p>
                1.1. Бул шәртнама бойынша Қарыз бериуши Қарыз алыушыға <b>{{ nf($shopOrder->total) }}</b> сум
                мугдардағы жәмийет өнимлерин мүлк кылып бериу мәжбүриятын алады. Қарыз алыушы болса бериушиге карызын
                бир жола ямаса бөлип-бөлип төлеу мәжбүриятын алады.
            </p>

            <div class="text-center">
                <h3>2.ТӘРЕПЛЕРДИҢ ХУҚЫҚ ХӘМ МӘЖБҮРИЯТЛАРЫ</h3>
            </div>

            <p>
                2.1. Қарыз бериуши шәртнаманың 1.1-бәнтинде нәзерде тутылған муғдарда Қарыз алыушыға қарыз суммасын
                <b>{{ dfd($shopOrder->installment_payments->last()->date) }}</b> күнине шекем бериу мәжбүриятын алады
                <br>
                2.2.Карыз алыушы: <br>
                2.2.1. Карыз суммасын <b>{{ dfd($shopOrder->installment_payments->last()->date) }}</b> күнине шекем
                Қарыз бериушиге кайтырыу; <br>
                2.2.2. Кестеде көрсеилген талапларды хәм шәртлерди орынланыуын тәминлеу. <br>
            </p>

            <div class="text-center">
                <h3>3.ТӘРЕПЛЕРДИҢ ЖУУАПКЕРШИЛИГИ ХӘМ ДАУАЛАРДЫ ШЕШИУ ТӘРТИБИ</h3>
            </div>

            <p>
                3.1. Тәреплер мәжбүриятларды орынламаған ямаса лазым дәрежеде орынламағанлығы ушын Өзбекистан
                Республткасы Пуқаралық кодекси хәм баска нызым хүжжетлери хәмде усы шәртанамаға тийкар жууапкер болады.
                <br>
                3.2. Тәреплер ортасында келип шыққан дауалар тәреплердиң өз ара келисимине тийкарланып шешиледи.
                Тәреплер келисимге келмеген жағдайда суд тәрепинен шешиледи <br>
            </p>

            <div class="text-center">
                <h3>4.ШӘРТНАМАНЫҢ БАСКА ШӘРТЕЛЕРИ</h3>
            </div>

            <p>
                4.1. Бул шәртнама Қарыз бериуши тәрепинен 1.1 бәнтиндеги белгиленген қарыз суммасы Қарыз алыушыға
                берилген куннен баслап күшке киреди хәм қарыз суммасы Қарыз бериушиге толық қайтарылған кунге шекем әмел
                етеди. <br>
                4.2. Шәртнамаға өзгерис хәм қосымша киритиу тәртиби тәреплер келисимине тийкар әмелге асырылады. <br>
                4.3 Шәртнама еки нусқада дузилип теӊдей юридикалық кушке ийе. <br>
            </p>

            <div class="text-center">
                <h3>5.КЕСТЕ</h3>
            </div>

            <p>
                5.1 Кестеде көрсетилген талаплардың хәм шәтлердиң орынланыуын тәмийнлеу.
            </p>

            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td style="width: 50%">
                            <p>
                                <b>Қарыз бериўши</b>
                            </p>
                            <p class="text-left">
                                <b>«Муратбек мебель»</b> ЖШЖ Нукус қаласы Таслақ елаты Жойбардағы н/ж <br>
                                <b>Тел</b> (61) 224-45-90 <br>
                                <b>е/б 20208000604786976001</b> <br>
                                <b>ИНН: 301228133 МФО: 01037</b> <br>
                            </p>
                        </td>
                        <td class="text-center" style="width: 50%; vertical-align: top">
                            <p>
                                <b>Қарыз алыўшы:</b>
                            </p>
                            <p class="text-left">
                                Ф.И.О: <b>{{ $shopOrder->customer->fullname }}</b><br>
                                Адрес:
                                <b>{{ $shopOrder->customer->address ?? '________________' }}</b><br>
                                Жумыс орны:
                                <b>{{ $shopOrder->customer->work ?? '________________' }}</b><br>
                                Телефон: <b>+998{{ $shopOrder->customer->phone }}</b><br>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

<script>
    function printDiv()
    {
        var divToPrint=document.getElementById('printarea');
        var newWin=window.open('','Print-Window');
        newWin.document.open();
        newWin.document.write('<html><head><link rel="stylesheet" href="{{ asset('print.css') }}"></head><body onload="window.print()">'+divToPrint.innerHTML+'</body></html>');
        newWin.document.close();
        setTimeout(function(){newWin.close();},10);
    }
</script>

</html>
