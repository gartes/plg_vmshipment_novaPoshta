"undefined"===typeof NovaPoshta&&(NovaPoshta={});
"undefined"===typeof NovaPoshta.Setting&&(NovaPoshta.Setting={});
"undefined"===typeof NP_MethodOrderEdit&&(NP_MethodOrderEdit={});

/**
 * Настройки объекта по умолчанию
 * @type {{chosen: {disable_search_threshold: number, no_results_text: string}}}
 * @private
 */
NP_MethodOrderEdit._Setting = {
    chosen:{
        disable_search_threshold: 10,
        no_results_text: "Не найдено!!!"
    },
};


NP_MethodOrderEdit.Init = ()=>{
    NovaPoshta.Warehouses.init();
    NP_MethodOrderEdit.Retable.Init();
    NP_MethodOrderEdit.logsInit();;
    NP_MethodOrderEdit.InitSetting();
    NP_MethodOrderEdit.autocompleteAdressInit();
    // let Valifator = new NP_MethodOrderEdit_Valifator();
};


NP_MethodOrderEdit.printDocument = ()=>{
    let InternetDocument_Ref  = jQuery('#nova_pochta_InternetDocument_Ref').val();
    let keyApi ;
    if ( typeof Joomla.getOptions === 'function'){
        keyApi = Joomla.getOptions('npKeyAPI');
    }else{
        keyApi = Joomla.optionsStorage.npKeyAPI;
    }
    window.open( 'https://my.novaposhta.ua/orders/printDocument/orders[]/'+InternetDocument_Ref+'/type/html/apiKey/' + keyApi );
};


NP_MethodOrderEdit.Retable={
    Init:()=>{
        let $= jQuery;

        $('#nova_pochta_VolumeGeneralParamsList').on('value-update',  (el)=>{
            NP_MethodOrderEdit.On._Weight_change();
            NP_MethodOrderEdit.On.setGeneralSize();

            // setTimeout( , 500) ;
         })
    },
};



NP_MethodOrderEdit.logsInit = ()=>{
    let NP = new JsXzlib_NovaPoshta();
    let NpOptions

    if (typeof Joomla.getOptions === 'function') {
        NpOptions = Joomla.getOptions('plg_novaPoshta');
    } else {
        NpOptions = Joomla.optionsStorage.plg_novaPoshta ;
    }




    let logs = NpOptions.logs ;
    if (NpOptions.logs.errors.length>0){
        setTimeout(()=>{
            NP.Noty.showLogs(logs);
        },1500);
    }
};

NP_MethodOrderEdit.autocompleteAdressInit = ()=>{
    var NP = new JsXzlib_NovaPoshta();
    NP.Setting.autocomplete = {
        element: '#nova_pochta_Address__street',
        streetType: '#nova_pochta_Address__streetType',
        RefStreet: '#nova_pochta_RefStreet',
    };

    NP.autocompleteAdressInit();
};




NP_MethodOrderEdit.On={}
NP_MethodOrderEdit.On._Volume_change = {}
NP_MethodOrderEdit.On.SeatsAmount_change = {}
NP_MethodOrderEdit.On.Weight_change = {}

NP_MethodOrderEdit.On.SizeList_change = {}
NP_MethodOrderEdit.On.setGeneralSize = {}
NP_MethodOrderEdit.On._Weight_change = {}




function  NP_MethodOrderEdit_Valifator(){
    let ValifatorSetting = {
        timeout:5650,
    } ;
    let $= jQuery;
    $('body').on("weready",()=>{
        document.formvalidator.setHandler('sizeField', function (value, e ) {

            return true;
        });
    });
    // Количество мест
    document.formvalidator.setHandler('RedeliveryString', function (value, e) {
        console.log(e.val())
        if (+e.val()<1 || e.val()==='' ){
            let order_total =  $('[name="order_total"]').val();
            e.val( order_total ) ;
            new Noty({
                type: 'error',
                layout: 'bottomLeft',
                text: 'Сумма обратной доставки взята из заказа',
                timeout: ValifatorSetting.timeout,
            }).show();
        }
        return true ;
    });
    document.formvalidator.setHandler('SeatsAmount', function (value, e) {
        if (+e.val()<1){
            e.val(1);
            new Noty({
                type: 'error',
                layout: 'bottomLeft',
                text: 'Количество мест не может быть меньше <b>1</b>',
                timeout: ValifatorSetting.timeout,
            }).show();
        }
        return true ;
    });
    /*Вес фактический*/
    document.formvalidator.setHandler('validWeight', function (value, e) {
        e.val(e.val().replace(",", "."));
        if (+e.val()<0.1){
            e.val(0.1)
            new Noty({
                type: 'error',
                layout: 'bottomLeft',
                text: 'Вес фактический минимальное значение <b>0.1</b>',
                timeout: ValifatorSetting.timeout,
            }).show();
        }
        return true ;
    });
    /* Объемный вес */
    document.formvalidator.setHandler('validVolumeGeneral', function (value, el) {
        let e = $(el);
        let v = $('#nova_pochta_VolumeWeight');
        e.val(e.val().replace(",", "."));

        let vl = +e.val() * 250;
        // 0.0004
        // 0.00000161
        if (vl < 0.0004) {
            e.val(0.0000016);
            vl = 0.0004;
            new Noty({
                type: 'error',
                layout: 'bottomLeft',
                text: 'Объемный вес - минимальное значение <b>0.0004</b>.',
                timeout: ValifatorSetting.timeout,
            }).show();
        }
        v.val(vl);
        return true;
    });


    this.Init = ()=>{
        console.log(document.formvalidator)
        console.log(Valifator);
        console.log(this);
    };



}



NP_MethodOrderEdit.On={
    // Изменение полей размеров в модальном окне
    SizeList_change:(evt)=>{
        evt.preventDefault();
        let $= jQuery;
        let e = $(evt.target);
        let p = e.closest('tr');
        let sizeField = p.find('.sizeField');
        let r = 1,ind=0;
        sizeField.each((i,fild)=>{
            let v = +$(fild).val();
            if (!v) return ;
            r=r*v;
            ind++;
        });
        if (ind!==3) return;
        r=r/1000000;
        if (r<0.0004) r=0.0004 ;
        p.find('input.sizeField_Volume').val(r);
        p.find('input.sizeField_VolumeWeight').val(r*250);
        NP_MethodOrderEdit.On.setGeneralSize();
    },
    // Изменение полей объем в содальном окне
    _Volume_change:(evt)=>{
        evt.preventDefault();
        let $= jQuery;
        let e = $(evt.target);
        let p = e.closest('tr');
        let r = (+e.val()*250);
        p.find('.sizeField').val('');
        p.find('input.sizeField_VolumeWeightl').val(r);
        NP_MethodOrderEdit.On.setGeneralSize();
    },
    // Изменение полей фактический вес в модальном окне
    _Weight_change:( )=>{
        //evt.preventDefault();
        let $= jQuery;

        let p = $('#nova_pochta_VolumeGeneralParamsList_table');
        let Weight = p.find('.sizeField_Weight');
        let val_W = 0;
        if (Weight.length===1){
            if (+Weight.val()<0.1){
                Weight.val(0.1);
            }
        }
        for (var i = 0; i < Weight.length; i++) {
            val_W += +$(Weight[i]).val();
        }
        if (val_W<0.1)  val_W =0.1 ;
        $('#nova_pochta_Weight').val(val_W).prop('readonly',true);
    },
    // Установка общих размеров
    setGeneralSize:()=>{
        let $= jQuery;
        let p = $('#nova_pochta_VolumeGeneralParamsList_table');
        let Volumes = p.find('.sizeField_Volume');
        let VolumeGenerals = p.find('.sizeField_VolumeWeight');
        let val_V = 0,val_VG=0;
        for (var i = 0; i < Volumes.length; i++) {
            val_V += +$(Volumes[i]).val();
            val_VG += +$(VolumeGenerals[i]).val();
        }
        if (val_VG<0.0004){
            val_VG=0.0004;
        }
        $('#nova_pochta_SeatsAmount').val(Volumes.length);
        $('#nova_pochta_VolumeWeight').val(val_VG);
        $('#nova_pochta_VolumeGeneral').val(val_V).prop('readonly',true);
    },

    // Кнопка создать ЕН
    intDocNumber_save:(evt)=>{
        evt.preventDefault();
        let $=jQuery ;
        let NP = new JsXzlib_NovaPoshta();

        let opt = NP.Setting.objDef;
        let form = $( evt.target ).closest('form');

        Xzlib.Helper.startPreload( NP.Setting.optPreload );

        opt.model='local';
        opt.task='saveIntDocNumber';
        NP.Ajax.Helper.send( form , opt ).then((r)=>{
            if (!r.data){
                new Noty({
                    type: 'error',
                    layout: 'bottomLeft',
                    text: r.message,
                    timeout: 3500,
                }).show();
            }
            NP.Noty.showLogs(r.data.INFO);
            console.log( r.data.nova_pochta.InternetDocument ) ;
            $.each(r.data.nova_pochta.InternetDocument,(i,v)=>{
                $('#nova_pochta_InternetDocument_'+i).val(v);
            });
            // Сохранить форму новой почты
            NP_MethodOrderEdit.On.saveNpForm(form);

        },(data)=>{
            console.log(data)
        });

    },
    /**
     *  Кнопка сохранить
     * @param evt
     */
    save:(evt)=>{
        evt.preventDefault();
        let form = jQuery ( evt.target ).closest('form');
        NP_MethodOrderEdit.On.saveNpForm(form);
    },
    saveNpForm:(form)=>{
        /**
         * @type {JsXzlib}
         */
        let NP = new JsXzlib_NovaPoshta();

        Xzlib.Helper.startPreload( NP.Setting.optPreload );
        let opt = NP.Setting.objDef;
        opt.model='local';
        opt.task='saveAdminForm';

        NP.Ajax.Helper.send( form , opt ).then(
            (data)=>{
                        NP_MethodOrderEdit.On.updRecipient(data.data);
                        NP.Noty.showLogs(data.data.INFO);
                        //console.log(data.data.INFO) ;
            },(data)=>{
                console.log( data ) ;
            });
    },
    updRecipient : (data)=>{
        let $=jQuery;
        $('#NovaPoshta_edit').find('#nova_pochta_RecipientAddressDoors').val(data.nova_pochta.RecipientAddressDoors);
        console.log(data.nova_pochta.RecipientAddressDoors)
    }

};


/**
 * init params Def
 * @constructor
 */
NP_MethodOrderEdit.InitSetting = ()=>{
    var $ = jQuery ;
    "undefined"===typeof NP_MethodOrderEdit.Setting&&(NP_MethodOrderEdit.Setting={});
    //var _s = NP_MethodOrderEdit.Setting;
    NP_MethodOrderEdit.Setting = $.extend( true , NP_MethodOrderEdit._Setting ,NP_MethodOrderEdit.Setting );
};

/**
 *
 * @type {{ CitySender: NP_MethodOrderEdit.Change.CitySender,
 *          WarehousesSender: NP_MethodOrderEdit.Change.WarehousesSender
 *          }}
 */
NP_MethodOrderEdit.Change={
    CitySender : (evt)=>{
        console.log('START')

        NovaPoshta.On.selectCity(evt)

        console.log('END')


/*
        var $ = jQuery ;
        var dataOptionCity = NovaPoshta.getDataOptionCity( $( evt.target ).find('option:selected') );
        if (dataOptionCity) {
            /!*
            $( evt.target ).parent().find('option.emptyOption').remove();
            $( evt.target ).trigger("liszt:updated");
            *!/

            // Отправить запрос для получения складов в выбранном городе
            NovaPoshta.getWarehouses  ( dataOptionCity , NP_MethodCart.updateWarehousesSelect_Callback );

        }//END IF*/
    },// END FN
    WarehousesSender:(evt)=>{
        var $=jQuery ;
        $( evt.target ).parent().find('option.emptyOption').remove();
        $( evt.target ).trigger("liszt:updated");

        var opt = { CounterpartyProperty:"Sender", };
        //NovaPoshta.getCounterpartiesAllData( opt , NP_MethodCart.getCounterparties_Callback );
    },// END FN
};














/*
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
            $('.additional_settings.openForm').removeClass('openForm').slideUp(/!*1,()=>{ }*!/);
        }

    });
};*/

//
/*

NP_MethodCart.CityRecipientInit = ()=>{
    jQuery('#CityRecipient').chosen(NP_MethodCart.Setting.chosen);
};

NP_MethodCart.RecipientAddresstInit = ()=>{
    jQuery('#nova_pochta_RecipientAddress').attr('data-placeholder' , 'Выбирите отделение...').chosen(NP_MethodCart.Setting.chosen);
};

*/


/**
 * Парсинг списка складов для города
 * @param r
 */
NP_MethodCart = {}
NP_MethodCart.updateWarehousesSelect_Callback = (r)=>{
    var $=jQuery ;
    var _fe = false ;
    if (r.data.info.totalCount > 1 )  _fe = true ;


    var opts = NovaPoshta.getWarehousesOptions( r.data.data , { firstEmpty : _fe , firstEmptyText : '*' } );
    $('#nova_pochta_RecipientAddress').empty().append(opts).trigger("liszt:updated");
};
NP_MethodCart.autocompleteAdressInit = ()=>{

    var $=jQuery ;
    var local_StreetsType;
    $('#nova_pochta_Address__street').autocomplete({
        minLength: 2 ,
        select: function( event, ui ) {

            local_StreetsType = ui.item.StreetsType ;
            console.log(ui.item.StreetsType) ;

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
            var textEnter = request.term.replace(local_StreetsType ,"");

            NovaPoshta.searchSettlementStreets  ( textEnter.trim() , dataOptionCity , NP_MethodCart.autocomplete_Callback );



        },
    });
};


document.addEventListener("DOMContentLoaded", function () {
    NP_MethodOrderEdit.Init();
});