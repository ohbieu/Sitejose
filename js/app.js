document.addEventListener('click', function(e){
    if (e.target.matches('.toggle-fav')){
        e.preventDefault();
        const id = e.target.dataset.id;
        fetch('/jogo3/favorite_toggle.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id)
        }).then(r=>r.json()).then(json=>{
            if (json.success){
                e.target.textContent = json.favorito==1 ? '★' : '☆';
            } else {
                alert('Erro: ' + (json.message || 'não foi possível'));
            }
        });
    }
});

// remove duplicate avatar file inputs if present (fix UI bug)
document.addEventListener('DOMContentLoaded', function(){
    try{
        const inputs = Array.from(document.querySelectorAll('input[type=file][name="avatar"]'));
        if (inputs.length > 1){
            // keep the first, remove others
            inputs.slice(1).forEach(i => i.remove());
        }
    }catch(e){/* ignore */}
});

// custom file button behavior
document.addEventListener('click', function(e){
    if (e.target.matches('.file-input-button') || e.target.closest('.file-input-button')){
        const btn = e.target.closest('.file-input-button');
        const wrapper = btn.closest('.file-input-wrapper');
        const input = wrapper.querySelector('input[type=file]');
        if (input) input.click();
    }
});

// Flash toast handling: show if #flash-toast exists
function handleFlashToast(){
    const toast = document.getElementById('flash-toast');
    if (!toast) return;
    const type = toast.dataset.type || 'success';
    toast.classList.add(type === 'error' ? 'error' : 'success');
    // trigger show
    setTimeout(() => toast.classList.add('show'), 50);
    // auto hide after 4s
    setTimeout(() => toast.classList.remove('show'), 4050);
    // remove from DOM after transition
    toast.addEventListener('transitionend', function(){
        if (!toast.classList.contains('show')){
            try{ toast.remove(); }catch(e){}
        }
    });
}

if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', handleFlashToast);
} else {
    // DOMContentLoaded already fired
    handleFlashToast();
}

// no change listener for file-name because filename display was removed
