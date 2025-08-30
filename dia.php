<div id="myModal" class="modal">
   <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Modal Header</h2>
   </div>
   <div class="modal-body">
      <p>Some text in the modal...</p>
      <p>Some more text here</p>
   </div>
   <div class="modal-footer">
      <h3>Modal Footer</h3>
   </div>
   </div>
</div>
<script>
var modal=document.getElementById("myModal");
var btn=document.getElementById("myBtn");
var span=document.getElementByIdClassName("close")[0];

btn.onclick=function(){
   modal.style.display="block"
   }

  span.onclick=function(){
   modal.style.display="none"
   }

   window.onclick=function(event){
  if(event.target==modal){

  modal.style.display="none";
  }
   }
</script>