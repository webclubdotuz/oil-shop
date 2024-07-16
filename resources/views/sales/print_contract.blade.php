<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Договор № {{ $sale->Ref }}</title>
    <link rel="stylesheet" href="/print.css">


</head>

<body>

    <button onclick="printDiv()" id="myBtn" title="Печать">Печать</button>

    <div class="a4" id="printarea">
        <div class="text-center">
            <h3>ДОГОВОР № <span style="color: red">{{ $sale->Ref }}</span></h3>
        </div>

        <div class="text-justify">
            <p class="text-center">
                <b>Купли – продажи с условием оплаты в рассрочки</b>
            </p>
            <p>
                <span class="align-left">
                    г. Taxiatosh
                </span>
                <span class="align-right">
                    {{ df($sale->date) }}
                </span>
            </p>

            <p>
                Taxiatosh TRIO MCHJ, именуемый в лице Реимов.Б дальнейшем «Продавец», действующий на основании
                Свидетыльства, с одной стороны , гражданин (ка) <b>{{ $sale->client->username }}</b> работающий(ая) в на
                должности (паспорт серия {{ $sale->client->passport }} выдан {{ $sale->client->passport_date }} именуемый в дальнейшем «Покупатель» со второй
                стороны, именуемый в дальнейшем «Работадатель», в лице Директора действующего (ей) на основании Устава, с третьей стороны, заключили настоящий договор о нижеследующем:
            </p>

            <p class="text-center">
                <b>1. Предмет договора</b>
                <p>
                    1.1. Продавец обязуется передать Покупателя нижеследующие товары с условием оплаты в рассрочку сроком на
                    <b>{{ $installment_count }}</b> месяца (ев), при этом товары переходят в собственность Покупателя только после полной оплаты
                    Покупателем стоимости полученных товаров:
                </p>

                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td rowspan="2">№</td>
                            <td rowspan="2">Наименование товаров</td>
                            <td rowspan="2">IMEI коди</td>
                            <td rowspan="2">Кол-во</td>
                            <td rowspan="2">Цена товара с учетом рассрочки</td>
                            <td colspan="2">НДС</td>
                            <td rowspan="2">Стоимость поставки</td>
                        </tr>
                        <tr>
                            <td>%</td>
                            <td>Сумма</td>
                        </tr>

                        @foreach ($sale->details as $sale_detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sale_detail->product->name }}</td>
                            <td>{{ $sale_detail->imei_number }}</td>
                            <td>{{ $sale_detail->quantity }}</td>
                            <td>{{ nf($sale_detail->price) }}</td>
                            <td>{{ $sale_detail->TaxRate }}</td>
                            <td>{{ $sale_detail->TaxNet }}</td>
                            <td>{{ nf($sale_detail->total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Итого</td>
                            <td>{{ $sale->details->sum('quantity') }}</td>
                            <td>{{ nf($sale->GrandTotal) }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ nf($sale->GrandTotal) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <p>
                    1.2. Покупатель обязуется оплатить стоимость товаров на условиях указанных ниже.
                </p>
                <p>
                    1.3 Работадатель обязуется в соответствии с условиями настоящего договора удерживать из заработной платы, других выплат, в том числе пособия по соцстраху Покупателя определенные договором суммы и перечислять на расчетный счет Продавца № в (МФО ) до полного погашения Покупателем задолженности перед Продавцом.
                </p>
                <p>
                    1.4 Работадатель подтверждает, что Покупатель работает в вышеуказанной организации и на указанной должности
                </p>
                <p>
                    1.5 Работадатель подтверждает, что доходы Покупателя после обязательных и других платежей достаточны для покрытия платежей по данному договору.
                </p>
            </p>


            <p class="text-center">
                <b>2. ЦЕНА ДОГОВОРА</b>
                <p>
                    2.1 Цена настоящего договора составляет. <b>{{ nf($sale->GrandTotal) }} </b> ({{numberToWords('ru',$sale->GrandTotal)}})
                </p>
                <p>
                    2.2 Цена на товары могут быть пересмотрены в сторону увеличения по обоюдному согласию Продавца и Покупателя, в сторону уменьшения только с согласия Продавца.
                </p>
            </p>

            <p class="text-center">
                <b>3. ПОРЯДОК РАСЧЕТОВ</b>
                <p>
                    3.1 Покупатель обязан в течении 5 дней с даты подписания договора произвести предварительную оплату в размере {{ $first_payment_percent }}% от первоначальной цены товаров <b>{{ nf($first_payment) }} ( {{numberToWords('ru',$first_payment)}} )</b> наличными. или перечислением со своих или другого физического лица депозитных счетов. В случае не внесения указанной суммы в срок Продавец имеет право в одностороннем порядке расторгнуть договор.
                </p>
                <p>
                    3.2 Остальную сумму Работадатель должен удерживать из заработка Покупателя ежемесячно по <b>{{ nf($two_payment) }} ({{numberToWords('ru',$two_payment)}})</b> при его начислении до полного погашения долга и в 2-х дневный срок перечислять на расчетный счет Продавца.
                </p>
                <p>
                    3.3 Окончательный срок оплаты <b>{{ df($sale->installments->last()->date) }}</b>.
                </p>
            </p>

            <p class="text-center">
                <b>4. УСЛОВИЯ ПЕРЕДАЧИ ТОВАРОВ</b>
                <p>
                    4.1 Продавец обязуется передать Покупателю закупленные им товары со своего склада в 15-ти дневный срок после предварительной оплаты. Передача товаров оформляется приемо-сдаточным актом.
                </p>
                <p>
                    4.2 Товары должны соответствовать лействующим стандартам. Гарантийные сроки определяются производителями товаров.
                </p>
                <p>
                    4.3 Отбор товаров производится Покупателем самостоятельно или с привлечением специалистов.
                </p>
            </p>

            <p class="text-center">
                <b>5. ПРАВА И ОБЯЗАННОСТИ СТОРОН</b>

                <p>
                    5.1 Продавец имеет право: <br>
                    -требовать от Покупателя и Работадателя своевременной оплаты стоимости переданных товаров; <br>
                    -обращаться в нотариальную контору или суд с заявлением о взыскании в бесспорном порядке просроченной задолженности.
                </p>

                <p>
                    5.2 Продавец обязан: <br>
                    -передать покупателю в 2-х дневный срок после предварительной оплаты товары, соответствующие по качеству действующим стандартам;
                </p>
                <p>
                    5.2 Продавец обязан: <br>
                    -передать покупателю в 2-х дневный срок после предварительной оплаты товары, соответствующие по качеству действующим стандартам;
                </p>
                <p>
                    5.4 Покупатель обязан: <br>
                    -в установленные сроки произвести оплату за полученные от Продавца товары; <br>
                    -использовать товары по прямому назначению, обеспечить их надлежающую эксплуатацию и сохранность; <br>
                    -до полной оплаты стоимости товаров не продавать их и не совершать другие сделки; <br>
                    -в случае изменения места работы и жительства, фамилии и имени, других обстоятельств, препятствующих исполнению обязательств по настоящему договору, немедленно информировать об этом Продавца и досрочно, в 3-х дневный срок полностью рассчитать за полученные товары.
                </p>
                <p>
                    5.5 Работадатель обязан: <br>
                    -строго выполнять обязательства по настоящему договору, ежемесячно при начислении заработка удерживать на Покупателя определенные договором суммы и в 2-х дневный срок перечислять на расчетный счет Продавца; <br>
                    -немедленно информировать Продавца об увольнении Покупателя. Причитающуюся Покупателю при расчете сумму полностью или частично, в зависимости от размера долга, перечислять Продавцу.
                </p>
            </p>

            <p class="text-center">
                <b>6. ОТВЕТСТВЕННОСТЬ СТОРОН</b>

                <p>
                    6.1 Стороны несут взаимную ответственность за неисполнение и ненадлежащее исполнение обязательств по настоящему договору в соответствии с действующим законодательством .<br>
                    6.2 Все споры разногласия при исполнении настоящего договора разрешаются на дружественной основе, а при отсутствии согласия в судебном порядке.<br>
                    6.3 За нарушение сроков оплаты стоимости полученных товаров Покупатель обязан уплатить Продавцу пеню в размере 0.5% за каждый день просрочки, но не более 50% от неуплаченной в срок суммы.<br>
                    6.3 За нарушение сроков оплаты стоимости полученных товаров Покупатель обязан уплатить Продавцу пеню в размере 0.5% за каждый день просрочки, но не более 50% от неуплаченной в срок суммы.
                </p>
            </p>

            <p class="text-center">
                <b>7. ПРОЧИЕ УСЛОВИЯ</b>

                <p>
                    7.1 Изменение, дополнение и досрочное расторжение настоящего договора производится по письменному соглашению сторон. <br>
                    7.2 В случаях, не предусмотренных настоящим договором стороны руководствуются с действующим законодательством. <br>
                    7.3 Настоящий договор вступает в силу со дня его подписания и действует до полного выполнения, предусмотренных им обязательств. <br>
                    7.4 Настоящий договор составлен в 3-х экземплярах, по одному для каждой из сторон. <br>
                </p>

                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td valign="top">
                                <p class="text-center">
                                    <b>«ПРОДАВЕЦ»:</b> <br>
                                </p>
                                <p>
                                    Адр. Taxiatosh Mustaqillik <br>
                                    № р/с 20208000800862038001 <br>
                                    Банк XАЛК банк филиали <br>
                                    МФО: 00643 <br>
                                    ИНН: 305437764 <br>
                                    Тел: <br>
                                    +998912582626 <br> +998913882615 <br>

                                    Реимов.Б _______
                                </p>
                            </td>
                            <td valign="top">
                                <p class="text-center">
                                    <b>«РАБОТАДАТЕЛЬ»:</b>
                                </p>
                                <p>
                                    Адр. <br>
                                    № р/с <br>
                                    Банк  <br>
                                    МФО: <br>
                                    ИНН: <br>
                                    Тел: <br>

                                    Директор _______
                                </p>
                            </td>
                            <td valign="top">
                                <p class="text-center">
                                    <b>«ПОКУПАТЕЛЬ»</b>
                                </p>

                                {{ $sale->client->username }} <br>
                                {{ $sale->client->address }} <br>
                                {{ $sale->client->phone }} <br>
                                {{ $sale->client->passport }} <br>
                                {{ $sale->client->passport_iib }} <br>

                                <p>
                                    _______________
                                </p>
                            </td>
                        </tr>
                    </tbody>

                </table>


            </p>

            <div style="break-after:page"></div>

            <p class="text-center">
                <b>ТИЛХАТ</b>
            </p>

            <p>
                Мен, Taxiatosh, Aydin jol МФЙ, Pirjan Seyitov куча, уй №17 кучасида яшовчи фукаро <b>{{ $sale->client->username }}</b> Сизга маълумки, «Taxiatosh TRIO MCHJ»дан ва сиз томонингиздан <b>{{ df($sale->created_at) }}</b> й. кунги тузилган <b>{{ $sale->Ref }}</b> сонли олди-сотди шартномасига асосан Сизнинг савдо дуконингиздан умумий суммаси <b>{{ nf($sale->GrandTotal) }}</b> сум булган, шундан <b>{{ $first_payment }}</b> сумни бошлангич бадал сифатида тулаб, колган <b>{{ nf($two_payment) }}</b> сумни <b>{{ $installment_count }}</b> ой давомида тулов графигида курсатилган кунларида тулаб бориш шарти билан сотиб олдим. <br> <br>
                Ушбу юкорида курсатилган туловларни ойма-ой уз вактида тулаб борилишини кафолатга оламан, кечиктарилган ойлик тулов суммасига шартномада белгиланган хар бир кечикган кунга <b>0,5%</b> пеня хисобланишига розилик билдираман. Туловлар <b>2</b> ой давомида кетма-кет туланмай колинган такдирда олинган жихозни олиб кетилишига ва хукукни мухофаза килиш органлари оркали ундириб олинишига розиман. <br>
            </p>
            <p class="text-center">
                Ушбу тилхат билан танишиб чикдим ва уз кулим билан имзоладим.
            </p>

            <p>
                <b>Фукаро:</b> {{ $sale->client->username }} <br>
                <b>{{ df($sale->created_at) }} й.</b>
            </p>

            <div style="break-after:page"></div>

            <p class="text-center">
                <b>МУДДАТЛИ ТЎЛОВ ШАРТНОМАСИ № {{ $sale->Ref }}</b>
                <table class="table" style="border: 1px solid white;">
                    <tbody>
                        <tr>
                            <td style="float: left;">
                                Уйчи ш
                            </td>
                            <td style="float: right;">
                                {{ df($sale->created_at) }} й.
                            </td>
                        </tr>
                    </tbody>
                </table>

                <br>
                <b>Мижоз маълумотлари:</b>
                <p class="text-center">
                    <b>{{ $sale->client->username }}</b> <br>
                    _________________________________________________________________
                </p>

                <table class="table" style="border: 1px solid white;">
                    <tbody>
                        <tr>
                            <td style="float: left;">
                                <b>Паспорт серияси ва раками:</b> {{ $sale->client->passport }} <br>
                            </td>
                            <td style="float: right;">
                                <b>Телефон рақами:</b> {{ $sale->client->phone }} <br>
                            </td>
                            <td>
                                <b>Манзили:</b>
                            </td>
                            <td>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </p>

            <p class="text-center">
                <b>Товар маълумотлари</b>
            </p>

            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>№</td>
                        <td>Махсулот номи</td>
                        <td>IMEI коди</td>
                        <td>Нархи</td>
                        <td>Микдори</td>
                        <td>Суммаси</td>
                    </tr>
                    @foreach ($sale->details as $sale_detail)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sale_detail->product->name }}</td>
                        <td>{{ $sale_detail->imei_number }}</td>
                        <td>{{ nf($sale_detail->price) }}</td>
                        <td>{{ $sale_detail->quantity }}</td>
                        <td>{{ nf($sale_detail->total) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="4">Жами</td>
                        <td>{{ $sale->details->sum('quantity') }}</td>
                        <td>{{ nf($sale->GrandTotal) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="text-center">
                <b>Тулов графиги</b>
            </p>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>№</td>
                        <td>Тулов санаси</td>
                        <td>Тулов суммаси</td>
                    </tr>
                    @foreach ($sale->installments as $installment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ df($installment->date) }}</td>
                        <td>{{ nf($installment->amount) }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="2">Жами</td>
                        <td>{{ nf($sale->GrandTotal) }}</td>
                    </tr>
                </tbody>
            </table>

            <br>

            <table class="table" style="border: 1px solid white;">
                <tbody>
                    <tr>
                        <td style="float: left;">
                            <b>Шартноманинг умумий суммаси:</b>
                        </td>
                        <td style="float: right;">
                            <i>
                                {{ nf($sale->GrandTotal) }}
                            </i>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p>
                1.Агар бир томондан махсулот етказиб бериш бўйича ўзгариш бўлса, олдиндан ўзоро келишувга асосан узгартириш киритилади. <br>
                2.Шартнома бузилиб, ўзаро келишилмаган тақдирда Ўзбекистон Республикаси қонунлари асосида хал қилинади. <br>
                3."Харидор" шартномада кўрсатилгган қарздорликни тўлов муддатидан 10 кун кечиктирса шартномада курсатилгган махсулот муддатли тулов амалга оширмагунча олиб қўйилади. <br>
                4."Харидор" шартномада кўрсатилгган қарздорликни тўлаб булмагунча шартномада кўрсатилган махсулотни сотиш ёки совға қилиш мумкин эмас. <br>
            </p>

            <table class="table" style="border: 1px solid white;">
                <tbody>
                    <tr>
                        <td style="float: left;">
                            _________________Sabirov Javlonbek
                        </td>
                        <td style="float: right;">
                            _________________{{ $sale->client->username }}
                        </td>
                    </tr>
                </tbody>
            </table>


            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td valign="top">
                            <p class="text-center">
                                <b>«ПРОДАВЕЦ»:</b> <br>
                            </p>
                            <p>
                                Адр. Taxiatosh Mustaqillik <br>
                                № р/с 20208000800862038001 <br>
                                Банк XАЛК банк филиали <br>
                                МФО: 00643 <br>
                                ИНН: 305437764 <br>
                                Тел: <br>
                                +998912582626 <br> +998913882615 <br>

                                Реимов.Б _______
                            </p>
                        </td>
                        <td valign="top">
                            <p class="text-center">
                                <b>«ПОКУПАТЕЛЬ»</b>
                            </p>

                            {{ $sale->client->username }} <br>
                            {{ $sale->client->address }} <br>
                            {{ $sale->client->phone }} <br>
                            {{ $sale->client->passport }} <br>
                            {{ $sale->client->passport_iib }} <br>

                            <p>
                                _______________
                            </p>
                        </td>
                    </tr>
                </tbody>

            </table>


        </div>
    </div>
</body>

<script>
    function printDiv() {
        var divToPrint = document.getElementById('printarea');
        var newWin = window.open('', 'Print-Window');
        newWin.document.open();
        newWin.document.write(
            '<html><head><link rel="stylesheet" href="{{ asset('print.css') }}"></head><body onload="window.print()">' +
            divToPrint.innerHTML + '</body></html>');
        newWin.document.close();
        setTimeout(function() {
            newWin.close();
        }, 10);
    }
</script>

</html>
