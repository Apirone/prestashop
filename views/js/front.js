document.addEventListener("DOMContentLoaded", function(){const d=document;const c=d.getElementById('invoice_id');const a=c?c.value:null;const e=(ln=d.getElementById('statusUrl'))?ln.attributes.href.value:null;const f=e;let g=null;const h={method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}};async function i(){h.body=JSON.stringify({'invoice':a});const a=await fetch(f, h);const c=await a.text();return c}function j(){const a=d.querySelector('.btn.toggler');a&&a.addEventListener('click', function(){d.querySelector('.status-data').classList.toggle('historyFlag')})}async function k(){if(!f){return}h.body=JSON.stringify({'invoice':a,'offset':a});const a=await fetch(f, h);const c=await a.text();const d=d.getElementById('__apn-invoice');d.outerHTML=c;g=(stn=d.getElementById('statusNum'))?stn.value:0;l();j();m();n();stat=await i();async function e(){if(!g){clearInterval(processId);return}g=await i();g!==stat&&document.location.reload()};g>0&&(processId=setInterval(e, 5e3))}function l(){for(const e of d.querySelectorAll('.btn__copy'))e.addEventListener('click', function(){a(this)});function a(e){let i=e.firstChild;if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(i.value)}else{i.type="text";i.focus();i.select();d.execCommand('copy');i.type="hidden"}e.classList.toggle('copied');setTimeout(function(){e.classList.toggle('copied')}, 1000, e)}}function m(){expire=d.getElementById('expire');if(expire===null||expire.value<=0){return}let a=d.getElementById('countdown');if(a===null){return}const d=setInterval(()=>{const a=Math.floor(c/(60*60*24));const c=Math.floor((c%(60*60*24))/(60*60));const d=Math.floor((c%(60*60))/60);const e=Math.floor((c%60));a.innerHTML=a>0?`${a}d `:''+c>0?`${c}h `:''+d>0?`${d}m `:''+`${e}s`;c-=1;c<0&&(clearInterval(d),a.innerHTML='',document.location.reload())}, 1000)}function n(){let a=d.getElementById('linkback');let c=d.getElementById('linkback-counter');if(a===null||c===null){return}let d=c.innerHTML;let e=a.attributes.href.value;const f=setInterval(()=>{c.innerHTML=d;d-=1;!d&&(clearInterval(f),d.location.href=e)}, 1000)}k()});
