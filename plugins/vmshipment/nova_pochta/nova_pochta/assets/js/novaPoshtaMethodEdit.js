"undefined"===typeof NovaPoshta&&(NovaPoshta={});
"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
"undefined"===typeof NP_MethodEdit&&(NP_MethodEdit={});

NP_MethodEdit.SETING = {
    language : 'Ru' ,
};


NP_MethodEdit.Init = function () {
    var $ = jQuery;
    var t = $("#params_keyAPI").val();
    var e = $('[name="virtuemart_shipmentmethod_id"]').val();
    if (t.length > 0) {
        if (void 0 === NovaPoshta.Setting.keyAPI) {
            NovaPoshta.Setting.keyAPI = t
        }
        if (void 0 === NovaPoshta.Setting.methodId) {
            e.length > 0 && (NovaPoshta.Setting.methodId = e)
        }
        var opt = {CounterpartyProperty: "Sender",};
        NovaPoshta.getCounterpartiesAllData(opt, NP_MethodEdit.getCounterparties_Callback);
    }
    NP_MethodEdit.addBtnKeyControl();
};



/**
 * Создать кнопку Дальше
 */
NP_MethodEdit.addBtnKeyControl =()=>{
    var $ = jQuery ;
    $('#params_keyAPI').parent().append($('<div />' , {
        class: 'runBatton' ,
        text : '+Дальше+' ,
        click : ()=>{
            NovaPoshta.Setting.keyAPI = $('#params_keyAPI').val();
            NovaPoshta.getCity('' , NP_MethodEdit.onSetNewKeyCallback  );
        }
    }));
};



NP_MethodEdit.onSetNewKeyCallback = function(r){
    if(!NovaPoshta.Helper.checkResponse(r)) return ;
    var $ = jQuery ;
    var opts = NovaPoshta.getCityOptions( r.data.data , {firstEmpty : true } );
    $('#CitySender').empty().append(opts).trigger("liszt:updated")/*.off('change.plgNp').on('change.plgNp', NP_MethodEdit.Change.CitySender )*/;
};

NP_MethodEdit.Change = {
    CitySender:(el)=>{

        var $ = jQuery ;
        var dataOptionCity = NovaPoshta.getDataOptionCity( $( el.target ).find('option:selected') );
        if (dataOptionCity) {

            $( el.target ).parent().find('option.emptyOption').remove();
            $( el.target ).trigger("liszt:updated");

            // Отправить запрос для получения складов в выбранном городе
            NovaPoshta.getWarehouses  ( dataOptionCity , NovaPoshta.Setting.methodId, NP_MethodEdit.updateWarehousesSelect_Callback );

        }//END IF
    },// END FN
    WarehousesSender:(el)=>{
        var $=jQuery ;
        $( el.target ).parent().find('option.emptyOption').remove();
        $( el.target ).trigger("liszt:updated");

        var opt = { CounterpartyProperty:"Sender", };
        NovaPoshta.getCounterpartiesAllData( opt ,NP_MethodEdit.getCounterparties_Callback);
    },// END FN

};


/**
 * Парсинг списка складов для города
 * @param r
 */
NP_MethodEdit.updateWarehousesSelect_Callback = (r)=>{
    var $=jQuery ;
    var opts = NovaPoshta.getWarehousesOptions( r.data.data , {firstEmpty : true } );
    $('#params_SenderAddress').empty().append(opts).trigger("liszt:updated");
};

NP_MethodEdit.getCounterparties_Callback= (r)=>{
    var Counterparties = r.data.data[0] ;
    var ContactPersons = Counterparties.ContactPersons.data[0] ;
    var $=jQuery ;

    $('#params_SenderCounterpartyText').val(Counterparties.Description);
    $('#params_Sender').val(Counterparties.Ref);

    $('#params_SenderContactText').val(ContactPersons.Description);
    $('#params_ContactSender').val(ContactPersons.Ref);

    $('#params_SenderContactPhoneText').val(ContactPersons.Phones);




    console.log( r  ) ;
};

document.addEventListener("DOMContentLoaded", function () {

     NP_MethodEdit.Init();
});










































