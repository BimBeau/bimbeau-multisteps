(function(){
    document.addEventListener('DOMContentLoaded', function(){
        var filter = document.getElementById('ms-data-view-filter');
        if (!filter) return;
        filter.addEventListener('keyup', function(){
            var term = filter.value.toLowerCase();
            document.querySelectorAll('.ms-data-view tbody tr').forEach(function(row){
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    });
})();
