(function(){
    var selectors = document.getElementsByClassName('showType');
    var posts = document.getElementsByClassName('post');

    for (var i = selectors.length - 1; i >= 0; --i) {
        selectors[i].addEventListener('change', function() {
            for (var j = posts.length - 1; j >= 0; --j) {
                if (posts[j].getAttribute('data-status') != this.value) {
                    continue;
                }
                posts[j].style.display = (this.checked ? 'block' : 'none');
            }
        });

        selectors[i].checked = (i == 0);
        var event = new CustomEvent('change');
        selectors[i].dispatchEvent(event);
    }

    for (var i = posts.length - 1; i >= 0; --i) {
        var post = posts[i];
        var selector = post.getElementsByClassName('stateChanger')[0]
        selector.addEventListener('change', function() {
            var status = this.value;
            this.setAttribute('disabled', 'disabled');


            var xhttp = new XMLHttpRequest();
            xhttp.open('POST', 'api.php', true);

            var data = new FormData();
            data.append('act', 'updatePost');
            data.append('ai', post.getAttribute('data-ai'));
            data.append('status', status);

            xhttp.send(data);
            xhttp.onreadystatechange = function () {
                if(this.readyState != 4) return ;

                var data;
                try {
                    if (this.status != 200) throw (this.statusText || 'bad response');
                    try {
                        data = JSON.parse(this.responseText);
                    } catch(e) {throw 'bad response';}
                    if (!data.success) throw (data.message || 'unknown error');
                } catch(e) {
                    console.log('Error in: ', this);
                    alert('Error: ' + e);
                    return;
                }

                post.setAttribute('data-status', status);
                post.style.display = (selectors[status].checked ? 'block' : 'none');

                selector.removeAttribute('disabled');
            };
        });
    }
})();

function update()
{
    var container = this.parentNode;
    container.style.opacity = 0.5;
    container.style.pointerEvents = 'none';

    var xhttp = new XMLHttpRequest();
    xhttp.open('POST', 'api.php', true);

    var data = new FormData();
    data.append('act', 'update');

    xhttp.send(data);
    xhttp.onreadystatechange = function () {
        if(this.readyState != 4) return ;
        container.style.opacity = 1;
        container.style.pointerEvents = null;

        var data;
        try {
            if (this.status != 200) throw (this.statusText || 'bad response');
            try {
                data = JSON.parse(this.responseText);
            } catch(e) {throw 'bad response';}
            if (!data.success) throw (data.message || 'unknown error');
        } catch(e) {
            console.log('Error in: ', this);
            alert('Error: ' + e);
            return;
        }

        alert('Found ' + data.found + ' posts');
		location.href = location.href;
		location.reload();
    };
}
