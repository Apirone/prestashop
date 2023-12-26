/**
 * 2017-2023 apirone.com
 *
 * NOTICE OF LICENSE
 *
 * This source file licensed under the MIT license 
 * that is bundled with this package in the file LICENSE.txt.
 * 
 * @author    Apirone OÜ <support@apirone.com>
 * @copyright 2017-2023 Apirone OÜ
 * @license   https://opensource.org/license/mit/ MIT License
 */
document.addEventListener('DOMContentLoaded',function(){var d=document,b=new Date().getTimezoneOffset(),c=d.getElementById('invoice_id'),a=c?c.value:null,A=(ln=d.getElementById('statusUrl'))?ln.attributes.href.value:null,g={method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}};let f=null;async function h(){g.body=JSON.stringify({'invoice':a});var B=await fetch(A,g);return await B.text()}function _(){var C=d.querySelector('.btn.toggler');C&&C.addEventListener('click',function(){d.querySelector('.status-data').classList.toggle('historyFlag')})}async function j(){if(!A)return;g.body=JSON.stringify({'invoice':a,'offset':b});var _a=await fetch(A,g);var _b=d.getElementById('__apn-invoice');_b.outerHTML=await _a.text();f=(stn=d.getElementById('statusNum'))?stn.value:0;k();_();l();n();stat=await h();async function _c(){if(!f){clearInterval(processId);return}f=await h();f!==stat&&document.location.reload()};f>0&&(processId=setInterval(_c,5e3))}function k(){for(const e of d.querySelectorAll('.btn__copy'))e.addEventListener('click',function(){D(this)});function D(e){let i=e.firstChild;navigator.clipboard&&window.isSecureContext?navigator.clipboard.writeText(i.value):i.type='text';e.classList.toggle('copied');setTimeout(function(){e.classList.toggle('copied')},1000,e)}}function l(){expire=d.getElementById('expire');if(expire==null||expire.value<=0)return;let _A=d.getElementById('countdown');if(_A==null)return;let _B=expire.value;var _C=setInterval(()=>{const E=m(~~_B/(60*60*24)),aA=m(~~(_B%(60*60*24))/(60*60)),aB=m(~~(_B%(60*60))/60),_d=m(~~(_B%60));_A.innerHTML=(E>0?`${E}d `:'')+(aA>0?`${aA}h `:'')+(aB>0?`${aB}m `:'')+`${_d}s`;_B-=1;_B<0&&(clearInterval(_C),_A.innerHTML='',document.location.reload())},1000)}function m(aC){return aC|0}function n(){let aD=d.getElementById('linkback');let aE=d.getElementById('linkback-counter');if(aD==null||aE==null)return;let aF=aE.innerHTML;let _D=aD.attributes.href.value;var _e=setInterval(()=>{aE.innerHTML=aF;aF-=1;!aF&&(clearInterval(_e),d.location.href=_D)},1000)}j()});
