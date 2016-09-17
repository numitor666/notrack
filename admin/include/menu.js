function PauseNoTrack(Action, PauseTime) {
  switch (Action) {
    case 'pause':
      document.getElementById("dialogmsg").innerHTML = "Pausing NoTrack for "+PauseTime+" minutes";
      document.getElementById("pause-time").value = "pause"+PauseTime;
      break;
    case 'start':
      document.getElementById("dialogmsg").innerHTML = "Enabling NoTrack";
      document.getElementById("pause-time").value = "start";      
      break;
    case 'stop':
      document.getElementById("dialogmsg").innerHTML = "Disabling NoTrack";
      document.getElementById("pause-time").value = "stop";      
      break;
    case 'force-notrack':
      document.getElementById("dialogmsg").innerHTML = "Updating Blocklists";
      document.getElementById("operation").value = "force-notrack";
      break;
    case 'restart':
      document.getElementById("dialogmsg").innerHTML = "Restarting System";
      document.getElementById("operation").value = "restart";
      break;
    case 'shutdown':
      document.getElementById("dialogmsg").innerHTML = "Shutting Down System";
      document.getElementById("operation").value = "shutdown";
      break;    
  }
  
  document.getElementById("fade").style.top=window.pageYOffset+"px";
  document.getElementById("fade").style.display = "block";
  
  document.getElementById("options-box").style.display = "none";
    
  document.getElementById("dialog-box").style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
  document.getElementById("dialog-box").style.left = (window.innerWidth / 2)+"px";
  document.getElementById("dialog-box").style.display = "block";
  
  if (Action == "pause" || Action == "start" || Action == "stop") {    
    document.forms["pause-form"].submit();
  }
  else {    
    document.forms["operation-form"].submit();
  }  
}
//Options Box--------------------------------------------------------
function ShowOptions() {  
  document.getElementById("fade").style.top=window.pageYOffset+"px";
  document.getElementById("fade").style.display = "block";
    
  document.getElementById("options-box").style.top = (window.pageYOffset + (window.innerHeight / 2))+"px";
  document.getElementById("options-box").style.left = (window.innerWidth / 2)+"px";
  document.getElementById("options-box").style.display = "block";
}
function HideOptions() {
  document.getElementById("options-box").style.display = "none";
  document.getElementById("fade").style.display = "none";
}
//-------------------------------------------------------------------
function openNav() {  
  if (typeof window.orientation !== 'undefined') {
    if (document.getElementById("menu-side").style.width == "0px" || document.getElementById("menu-side").style.width == "") {
      document.getElementById("menu-side").style.width = "210px";
      document.getElementById("main").style.marginLeft = "210px";
    }
    else {
      document.getElementById("menu-side").style.width = "0px";
      document.getElementById("main").style.marginLeft= "0px"; 
    }
  }
  else {    
    if (document.getElementById("menu-side").style.width == "210px" || document.getElementById("menu-side").style.width == "") {
      document.getElementById("menu-side").style.width = "50px";
      document.getElementById("main").style.marginLeft= "50px";
    }
    else {      
      document.getElementById("menu-side").style.width = "210px";
      document.getElementById("main").style.marginLeft = "210px";
    }
  }
}
