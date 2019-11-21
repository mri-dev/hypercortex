<div class="wrapper">
  <div class="line">
    <div class="form-input-holder">
      <div class="flextbl auto-width">
        <div class="h"><label for="cegnev"><?=__('Cégnév')?> *</label></div>
        <div class="inp" style="flex-basis:70%;"><input type="text" name="f_9" id="cegnev" class="form-control" value=""></div>
      </div>
    </div>
  </div>
  <div class="line">
    <div class="form-input-holder">
      <div class="flextbl auto-width">
        <div class="h"><label for="subscr"><?=__('E-mail cím')?> *</label></div>
        <div class="inp" style="flex-basis:70%;"><input type="text" name="subscr" id="subscr" class="form-control" value=""></div>
      </div>
    </div>
  </div>

  <div class="divider"></div>
  <div class="line">
    <div class="accepts">
      <div class=""><input type="checkbox" name="f_12[]" id="adatvedelem" value="2"> <label for="adatvedelem">* A feliratkozással elfogadja az <a href="/adavedelmi-nyilatkozat/" target="_blank">Adatvédelmi Nyilatkozatot</a> és hozzájárulok az adataim kezeléséhez.</label></div>
      <div class=""><input type="checkbox" name="f_11[]" id="marketing" value="1"> <label for="marketing">* Hozzájárulok, hogy az általam megadott e-mail címre időközönként üzleti céllal elektronikus levelet küldhetnek!</label></div>
    </div>
  </div>

  <div class="btns">
    <button type="submit" class="grad-button" name="sub" value="1" onClick="if(!fvalidate_3('feliratkozo_form_3')) return false;"><?=__('Feliratkozás')?></button>
  </div>
</div>
<input type="hidden" name="f_10" value="hypercortex.hu web feliratkozás">
<!-- WebGalamb Hírlevélkód - form vége
     JavaScript ellenőrző kód eleje -->
<script type="text/javascript">function checkf_11(){
 var inputs= document.getElementsByTagName("input");
 for(var i= 0; i< inputs.length; i++){
 if(inputs[i].name == "f_11[]" && inputs[i].checked){
 return 0;
 }}
 return 1; }
function checkf_12(){
 var inputs= document.getElementsByTagName("input");
 for(var i= 0; i< inputs.length; i++){
 if(inputs[i].name == "f_12[]" && inputs[i].checked){
 return 0;
 }}
 return 1; }

function fvalidate_3(fname)
{
   var formx=document.getElementById(fname);
   var hiba='';
   var mregexp=/^([a-zA-Z0-9_\.\-\+&])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,6})+$/;
   if(!mregexp.test(formx.subscr.value))
   {
     hiba='* Hibás a megadott e-mail cím!';
     formx.subscr.focus();
   }

   if (!formx.f_9.value)
     {
   hiba+= (hiba?"\n":'')+'* Cégnév nincs megadva!';
   formx.f_9.focus();
     }
 if(checkf_11()) hiba+= (hiba?"\n":'')+'* Hozzájárulok, hogy az általam megadott e-mail címre időközönként üzleti céllal elektronikus levelet küldhetnek! nincs megadva!';
 if(checkf_12()) hiba+= (hiba?"\n":'')+'* A feliratkozással elfogadja az Adatvédelmi Nyilatkozatot és hozzájárulok az adataim kezeléséhez. nincs megadva!';
 if(hiba)alert(hiba); else return true;
}
</script>
<!-- JavaScript ellenőrző kód vége -->
