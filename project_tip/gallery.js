(() => {
  const items = Array.from(document.querySelectorAll('[data-gallery]'));
  if (!items.length) return;

  const overlay = document.createElement('div');
  overlay.className = 'gallery-overlay';
  overlay.innerHTML =
    '<button class="gallery-close" type="button" aria-label="ปิด">×</button>' +
    '<div class="gallery-modal"><div class="gallery-main">' +
    '<span class="gallery-count"></span><button class="gallery-arrow gallery-prev" type="button">‹</button>' +
    '<img class="gallery-image" alt=""><button class="gallery-arrow gallery-next" type="button">›</button>' +
    '</div><aside class="gallery-side"><span class="gallery-kicker">TAK EXPLORE · GALLERY</span>' +
    '<h2 class="gallery-title"></h2><div class="gallery-location"></div><p class="gallery-desc"></p>' +
    '<div class="review-box"><div class="review-summary"><div><strong class="review-average">0.0</strong><span class="review-stars">★★★★★</span><small class="review-count">ยังไม่มีรีวิว</small></div><span class="review-badge-modal"></span></div>' +
    '<div class="review-list"></div><div class="review-form-wrap"></div></div>' +
    '<div class="gallery-thumbs"></div><div class="gallery-hint">← → เลื่อนรูป · ปัดบนมือถือ · ESC ปิด</div>' +
    '</aside></div>';
  document.body.appendChild(overlay);

  const img=overlay.querySelector('.gallery-image'), title=overlay.querySelector('.gallery-title');
  const loc=overlay.querySelector('.gallery-location'), desc=overlay.querySelector('.gallery-desc');
  const thumbs=overlay.querySelector('.gallery-thumbs'), count=overlay.querySelector('.gallery-count');
  const avgEl=overlay.querySelector('.review-average'), starsEl=overlay.querySelector('.review-stars');
  const countEl=overlay.querySelector('.review-count'), listEl=overlay.querySelector('.review-list');
  const formWrap=overlay.querySelector('.review-form-wrap'), badgeEl=overlay.querySelector('.review-badge-modal');

  let gallery=[], index=0, touchX=0, reviewType='', reviewId='', reviewLoggedIn=false, reviewCard=null;

  function render(){
    const item=gallery[index], src=typeof item==='string'?item:item.src;
    img.src=src; img.alt=item.alt||title.textContent; count.textContent=(index+1)+' / '+gallery.length;
    thumbs.innerHTML='';
    gallery.forEach((g,i)=>{const gs=typeof g==='string'?g:g.src; const b=document.createElement('button'); b.className='gallery-thumb'+(i===index?' active':''); b.type='button'; const t=document.createElement('img'); t.src=gs; t.alt=''; b.appendChild(t); b.onclick=()=>{index=i;render()}; thumbs.appendChild(b);});
  }

  function stars(n){return '★★★★★'.split('').map((x,i)=>i<n?'★':'☆').join('');}

  function applyReviewResult(data){
    if(!data || !data.ok) return;
    avgEl.textContent=Number(data.average||0).toFixed(1);
    starsEl.textContent=stars(Math.round(Number(data.average||0)));
    countEl.textContent=Number(data.count||0)>0?data.count+' รีวิว':'ยังไม่มีรีวิว';
    badgeEl.textContent=data.count>=10?'ยอดนิยม':(data.count>=3&&Number(data.average)>=4.3?'แนะนำ':'');
    if(data.review){
      const r=data.review;
      const el=document.createElement('div'); el.className='review-item review-item-new';
      const date=new Date(String(r.created_at||'').replace(' ','T'));
      el.innerHTML='<div class="review-top"><b></b><span></span></div><div class="review-text"></div>';
      el.querySelector('b').textContent=r.username_account||'คุณ';
      el.querySelector('span').textContent=stars(Number(r.rating))+' · '+(isNaN(date.getTime())?'เมื่อสักครู่':date.toLocaleDateString('th-TH'));
      el.querySelector('.review-text').textContent=r.review_text||'ให้คะแนนสถานที่นี้';
      const first=listEl.querySelector('.review-item');
      if(first) listEl.insertBefore(el,first); else {listEl.innerHTML='';listEl.appendChild(el);}
      while(listEl.children.length>5) listEl.removeChild(listEl.lastElementChild);
    }
  }

  async function deleteReview(reviewId){
    if(!reviewId || !confirm('ต้องการลบรีวิวรายการนี้ใช่ไหม?')) return;
    const fd=new FormData();
    fd.append('action','delete'); fd.append('review_id',reviewId); fd.append('item_type',reviewType); fd.append('item_id',reviewIdForStats());
    try{
      const res=await fetch(window.location.origin+'/review-submit.php',{method:'POST',body:fd,credentials:'same-origin',cache:'no-store'});
      const data=await res.json();
      if(!data.ok) throw new Error(data.message||'delete');
      await loadReviews();
    }catch(err){ alert(err.message||'ลบรีวิวไม่สำเร็จ'); }
  }
  function reviewIdForStats(){ return reviewId; }

  async function loadReviews(){
    if(!reviewType||!reviewId)return;
    listEl.innerHTML='<div class="review-loading">กำลังโหลดรีวิว...</div>';
    try{
      const apiUrl=window.location.origin+'/reviews-api.php?type='+encodeURIComponent(reviewType)+'&id='+encodeURIComponent(reviewId);
      const controller=new AbortController();
      const timeout=setTimeout(()=>controller.abort(),5000);
      const res=await fetch(apiUrl,{cache:'no-store',credentials:'same-origin',signal:controller.signal});
      clearTimeout(timeout);
      if(!res.ok) throw new Error('HTTP '+res.status);
      const data=await res.json();
      if(!data.ok)throw new Error();
      avgEl.textContent=Number(data.average||0).toFixed(1);
      starsEl.textContent=stars(Math.round(Number(data.average||0)));
      countEl.textContent=data.count?data.count+' รีวิว':'ยังไม่มีรีวิว';
      badgeEl.textContent=data.count>=10?'ยอดนิยม':(data.count>=3&&Number(data.average)>=4.3?'แนะนำ':'');
      listEl.innerHTML='';
      if(data.reviews.length){
        data.reviews.slice(0,5).forEach(r=>{
          const el=document.createElement('div'); el.className='review-item';
          const date=new Date(r.created_at.replace(' ','T'));
          el.innerHTML='<div class="review-top"><b></b><span></span></div><div class="review-text"></div><div class="review-actions"></div>';
          el.querySelector('b').textContent=r.username_account||'สมาชิก';
          el.querySelector('span').textContent=stars(Number(r.rating))+' · '+date.toLocaleDateString('th-TH');
          el.querySelector('.review-text').textContent=r.review_text||'ให้คะแนนสถานที่นี้';
          if(r.can_delete){
            const del=document.createElement('button');
            del.type='button'; del.className='review-delete-item'; del.textContent='ลบรีวิวนี้';
            del.onclick=()=>deleteReview(Number(r.id_review));
            el.querySelector('.review-actions').appendChild(del);
          }
          listEl.appendChild(el);
        });
      }else listEl.innerHTML='<div class="review-empty">ยังไม่มีรีวิว เป็นคนแรกที่รีวิวสถานที่นี้ได้เลย</div>';
      formWrap.innerHTML='';
      if(data.logged_in){
        const mine=data.mine||{};
        const form=document.createElement('form'); form.className='review-form';
        form.innerHTML='<div class="form-title">'+(mine.id_review?'แก้ไขรีวิวของคุณ':'เขียนรีวิวของคุณ')+'</div><div class="star-picker"></div><textarea maxlength="500" placeholder="เล่าประสบการณ์ของคุณ (ไม่บังคับ)"></textarea><button type="submit">บันทึกรีวิว</button>';
        const picker=form.querySelector('.star-picker');
        let selected=Number(mine.rating||0);
        for(let i=1;i<=5;i++){const b=document.createElement('button');b.type='button';b.textContent='★';b.className=i<=selected?'selected':'';b.onclick=()=>{selected=i;Array.from(picker.children).forEach((x,n)=>x.classList.toggle('selected',n<i))};picker.appendChild(b);}
        form.querySelector('textarea').value=mine.review_text||'';

        form.onsubmit=async e=>{e.preventDefault();if(!selected){alert('กรุณาเลือก 1-5 ดาว');return;}const btn=form.querySelector('button[type="submit"]');btn.disabled=true;btn.textContent='กำลังบันทึก...';const fd=new FormData();fd.append('item_type',reviewType);fd.append('item_id',reviewId);fd.append('rating',selected);fd.append('review_text',form.querySelector('textarea').value);try{const res=await fetch(window.location.origin+'/review-submit.php',{method:'POST',body:fd,credentials:'same-origin',cache:'no-store'});const data=await res.json();if(data.ok){applyReviewResult(data);form.querySelector('.form-title').textContent='รีวิวของคุณถูกบันทึกแล้ว ✓';form.querySelector('textarea').value=data.review?.review_text||'';btn.disabled=false;btn.textContent='บันทึกรีวิวอีกครั้ง';}else{alert(data.message||'บันทึกรีวิวไม่สำเร็จ');btn.disabled=false;btn.textContent='บันทึกรีวิว';}}catch(err){alert('เชื่อมต่อระบบรีวิวไม่สำเร็จ');btn.disabled=false;btn.textContent='บันทึกรีวิว';}};
        formWrap.appendChild(form);
      }else{
        formWrap.innerHTML='<a class="review-login" href="form-login.php">เข้าสู่ระบบเพื่อให้คะแนนและเขียนรีวิว →</a>';
      }
    }catch(e){
      listEl.innerHTML=reviewLoggedIn?'':'<div class="review-empty">ยังไม่สามารถโหลดรีวิวได้ แต่คุณสามารถเข้าสู่ระบบเพื่อให้คะแนนและเขียนรีวิวได้</div>';
      if(reviewLoggedIn){
        const form=document.createElement('form'); form.className='review-form';
        form.innerHTML='<div class="form-title">ให้คะแนนสถานที่นี้</div><div class="star-picker"></div><textarea maxlength="500" placeholder="เขียนรีวิวของคุณ"></textarea><button type="submit">บันทึกรีวิว</button>';
        const picker=form.querySelector('.star-picker'); let selected=0;
        for(let i=1;i<=5;i++){const b=document.createElement('button');b.type='button';b.textContent='★';b.className='';b.onclick=()=>{selected=i;Array.from(picker.children).forEach((x,n)=>x.classList.toggle('selected',n<i));};picker.appendChild(b);}
        form.onsubmit=async ev=>{ev.preventDefault();if(!selected){alert('กรุณาเลือกดาว 1–5 ดาว');return;}const fd=new FormData();fd.append('item_type',reviewType);fd.append('item_id',reviewId);fd.append('rating',selected);fd.append('review_text',form.querySelector('textarea').value);try{const res=await fetch(window.location.origin+'/review-submit.php',{method:'POST',body:fd,credentials:'same-origin'});const data=await res.json();if(data.ok){applyReviewResult(data);form.querySelector('.form-title').textContent='รีวิวของคุณถูกบันทึกแล้ว ✓';form.querySelector('textarea').value=data.review?.review_text||'';}else alert(data.message||'บันทึกรีวิวไม่สำเร็จ');}catch(err){alert('เชื่อมต่อระบบรีวิวไม่สำเร็จ');}};
        formWrap.innerHTML=''; formWrap.appendChild(form);
      }else{
        formWrap.innerHTML='<a class="review-login" href="form-login.php">เข้าสู่ระบบเพื่อให้คะแนนและเขียนรีวิว →</a>';
      }
    }
  }

  function open(card){
    try{gallery=JSON.parse(card.dataset.gallery||'[]')}catch(e){gallery=[]}
    if(!gallery.length)return;
    index=0; title.textContent=card.dataset.title||''; loc.textContent=card.dataset.location||'';
    desc.textContent=card.dataset.description||'ค้นพบมุมที่น่าสนใจของตาก';
    reviewCard=card; reviewType=card.dataset.reviewType||''; reviewId=card.dataset.reviewId||''; reviewLoggedIn=card.dataset.reviewLoggedIn==='1';
    avgEl.textContent=Number(card.dataset.reviewAvg||0).toFixed(1);
    starsEl.textContent=stars(Math.round(Number(card.dataset.reviewAvg||0)));
    countEl.textContent=Number(card.dataset.reviewCount||0)>0?card.dataset.reviewCount+' รีวิว':'ยังไม่มีรีวิว';
    render(); overlay.classList.add('is-open'); document.body.style.overflow='hidden'; loadReviews();
  }
  function close(){overlay.classList.remove('is-open');document.body.style.overflow='';}
  function next(step){if(!gallery.length)return;index=(index+step+gallery.length)%gallery.length;render();}

  items.forEach(card=>card.addEventListener('click',e=>{if(e.target.closest('button[data-place],button.trip-add-btn'))return;open(card);}));
  overlay.querySelector('.gallery-close').onclick=close;
  overlay.querySelector('.gallery-prev').onclick=()=>next(-1);
  overlay.querySelector('.gallery-next').onclick=()=>next(1);
  overlay.addEventListener('click',e=>{if(e.target===overlay)close();});
  document.addEventListener('keydown',e=>{if(!overlay.classList.contains('is-open'))return;if(e.key==='Escape')close();if(e.key==='ArrowLeft')next(-1);if(e.key==='ArrowRight')next(1);});
  img.addEventListener('touchstart',e=>{touchX=e.changedTouches[0].clientX},{passive:true});
  img.addEventListener('touchend',e=>{const dx=e.changedTouches[0].clientX-touchX;if(Math.abs(dx)>45)next(dx<0?1:-1)},{passive:true});
})();