"undefined"===typeof NovaPoshta&&(NovaPoshta={});
"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
"undefined"===typeof NP_MethodCart&&(NP_MethodCart={});
"undefined"===typeof VirtueMartCart_byPV&&(VirtueMartCart_byPV={});

console.log(VirtueMartCart_byPV)


NP_MethodCart._Setting = {
    chosen:{
        disable_search_threshold: 10,
        no_results_text: "Не найдено!!!",
        search_contains:true,
    },
};


NP_MethodCart.Init = ()=>{
    NP_MethodCart.InitSetting();

    NP_MethodCart.ShipmentmethodSelectInit();
    NP_MethodCart.autocompleteAdressInit();
    NP_MethodCart.CityRecipientInit();
    NP_MethodCart.RecipientAddresstInit();



};

/**
 * init params Def
 * @constructor
 */
NP_MethodCart.InitSetting = ()=>{
    var $ = jQuery ;
    "undefined"===typeof NP_MethodCart.Setting&&(NP_MethodCart.Setting={});
    var _s = NP_MethodCart.Setting;
    NP_MethodCart.Setting = $.extend( true , NP_MethodCart._Setting ,NP_MethodCart.Setting );
};







NP_MethodCart.ShipmentmethodSelectInit = ()=>{
    var $ = jQuery ;

     $('[name="virtuemart_shipmentmethod_id"]').on('change' , ( evt )=>{
        var id = $( evt.target ).val() ;
        var blk = $('.additional_settings.method_id'+id) ;
        if ( id == NP_MethodCart.Setting.method_id ){

            console.log(blk)
            if (!blk.hasClass('openForm')){
                blk.addClass('openForm').slideDown();
            }
        }else{
            $('.additional_settings.openForm').removeClass('openForm').slideUp(/*1,()=>{ }*/);
        }

    });
};

//

NP_MethodCart.CityRecipientInit = ()=>{
     jQuery('#CityRecipient').chosen(NP_MethodCart.Setting.chosen);

};

NP_MethodCart.RecipientAddresstInit = ()=>{
    jQuery('#nova_pochta_RecipientAddress').attr('data-placeholder' , 'Выбирите отделение...').chosen(NP_MethodCart.Setting.chosen);
};




NP_MethodCart.Change={
    CitySender : (evt)=>{
        var $ = jQuery ;
        var dataOptionCity = NovaPoshta.getDataOptionCity( $( evt.target ).find('option:selected') );


        console.log( dataOptionCity ) ;
        if (dataOptionCity) {

            let id = $( evt.target ).closest('form').find( 'input[name="method_id"]' ).val();

            /*
            $( evt.target ).parent().find('option.emptyOption').remove();
            $( evt.target ).trigger("liszt:updated");
            */


           console.log(dataOptionCity)


            // Отправить запрос для получения складов в выбранном городе
            NovaPoshta.getWarehouses  ( dataOptionCity ,id, NP_MethodCart.updateWarehousesSelect_Callback );

        }//END IF
    },// END FN
    WarehousesSender:(evt)=>{
        var $=jQuery ;
        $( evt.target ).parent().find('option.emptyOption').remove();
        $( evt.target ).trigger("liszt:updated");

        var opt = { CounterpartyProperty:"Sender", };
        //NovaPoshta.getCounterpartiesAllData( opt , NP_MethodCart.getCounterparties_Callback );
    },// END FN
};

/**
 * Парсинг списка складов для города
 * @param r
 */
NP_MethodCart.updateWarehousesSelect_Callback = (r)=>{
    var $=jQuery ;
    var _fe = false ;
    if (r.data.info.totalCount > 1 )  _fe = true ;


    var opts = NovaPoshta.getWarehousesOptions( r.data.data , { firstEmpty : _fe , firstEmptyText : '*' } );
    $('#nova_pochta_RecipientAddress').empty().append(opts).trigger("liszt:updated");
};



NP_MethodCart.autocompleteAdressInit = ()=>{

    var $=jQuery ;
    // var local_StreetsType;
    $('#nova_pochta_Address__street').autocomplete({
        minLength: 2 ,
        select: function( event, ui ) {

            // local_StreetsType = ui.item.StreetsType ;
            console.log(ui.item.StreetsType) ;
            $('#nova_pochta_Address__streetType').val(ui.item.StreetsType);
            $('#nova_pochta_RefStreet').val(ui.item.id);

        },

        source: (request, response)=>{

            NP_MethodCart.autocomplete_Callback = (r)=>{
                response($.map(r.data.data, function(item){

                    var t = (item.StreetsType + " " + item.Description).trim() ;
                    return{
                        StreetsType:item.StreetsType,
                        label: t ,
                        id: item.Ref ,
                        value: t ,
                    }
                }));
            };
            var dataOptionCity = NovaPoshta.getDataOptionCity( $('#CityRecipient').find('option:selected') );

            var local_StreetsType = $('#nova_pochta_Address__streetType').val();
            var textEnter = request.term.replace(local_StreetsType ,"");

            NovaPoshta.searchSettlementStreets  ( textEnter.trim() , dataOptionCity , NP_MethodCart.autocomplete_Callback );



        },
    });
};


document.addEventListener("DOMContentLoaded", function () {
    NP_MethodCart.Init();
});