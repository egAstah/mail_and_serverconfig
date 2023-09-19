$('.main__header-burger').click(function () {
    $('.main__content').toggleClass('openSide');
});

FontAwesomeConfig = {
    autoReplaceSvg: false
}

var dropZone = $('#upload-container');

dropZone.on('drag dragstart dragend dragover dragenter dragleave drop', function () {
    return false;
});

dropZone.on('dragover dragenter', function () {
    dropZone.addClass('dragover');
});

dropZone.on('dragleave', function (e) {
    dropZone.removeClass('dragover');
});

dropZone.on('drop', function (e) {
    dropZone.removeClass('dragover');
    $('.file-input').remove();
    $("#files")[0].files = e.originalEvent.dataTransfer.files;
    $.each($("#files")[0].files, function (key, input) {
        $('<div class="file-input">' + input.name + '</div>').insertAfter('#upload-container')
    });
});

$('#files').on('change', function () {
    $('.file-input').remove();
    $.each($("#files")[0].files, function (key, input) {
        $('<div class="file-input">' + input.name + '</div>').insertAfter('#upload-container')
    });
})

$(document).on('click', '#send-message', function (e) {
    e.preventDefault();
    if ($('.editor').text().replace(/\s+/g, '') != '') {
        let idSent = $('#main_list').val();
        let idCopy = $('#copy_list').val();
        var formData = new FormData();
        $.each($("#files")[0].files, function (key, input) {
            formData.append('file[]', input);
        });
        formData.append('event', 'send-message');
        formData.append('usersSent', idSent);
        formData.append('usersCopy', idCopy);
        formData.append('message', $('.editor').html());
        formData.append('subject', $('#subject-mail').val());
        $.ajax({
            url: '/local/ajax/mail-itiso.php',
            type: "post",
            contentType: false,
            processData: false,
            data: formData,
            success: function (res) {
                $('.editor').text('')
                $('#main_list').val('')
                $('#copy_list').val('')
                $("#files").val('')
                $('#subject-mail').val('')
                $('.file-input').remove()
                $('.success-send').show()
                $('.success-send').fadeOut(3000)
                // console.log(res)
            }
        });
    } else {
        alert('Заполните сообщение')
    }
});
$(document).on('click', '#decrypt-btn', function () {
    var formData = new FormData();
    if ($("#files")[0].files.length > 0) {
        $.each($("#files")[0].files, function (key, input) {
            formData.append('file[]', input);
        });
        formData.append('event', 'descrypt-message');
        $.ajax({
            url: '/local/ajax/mail-itiso.php',
            type: "post",
            contentType: false,
            processData: false,
            data: formData,
            success: function (res) {
                // console.log(res)
                if (res != 'error') {
                    res = $.parseJSON(res);
                    $('#info-user-from').html(res['info-user-from']);
                    $('#info-user-to').html(res['info-user-to']);
                    $('#subject-descypt').html(res['subject']);
                    $('#message-content').html(res['message']);
                    if (res['links']) {
                        let links = '';
                        for (let i = 0; i < res['links'].length; i++) {
                            links += res['links'][i];
                        }
                        $('#attachment').html(links)
                    }
                    $('#decrypt-mail').show();
                } else {
                    alert('Вы не можете просмотреть данное письмо')
                }
            }
        });
    } else {
        alert('Вы не выбрали ни одного файла')
    }
})

// $(document).on('click', '#delete-mail', function (e) {
//     e.preventDefault();
//     var formData = new FormData();
//     formData.append('event', 'delete-message');
//     formData.append('id', $(this).attr('data-id'));
//     $.ajax({
//         url: '/local/ajax/mail-itiso.php',
//         type: "post",
//         contentType: false,
//         processData: false,
//         data: formData,
//         success: function (res) {
//             let url = window.location.href;
//             url = url.split('?')[1].split('&')
//             location.href = '/?' + url[0]
//         }
//     });
// });
//
// $(document).on('click', '#save-drafts', function (e) {
//     e.preventDefault();
//     var formData = new FormData();
//     formData.append('event', 'save-drafts');
//     formData.append('message', $('.editor').html());
//     formData.append('subject', $('#subject-mail').val());
//     formData.append('user', $('#now-user').val());
//     $.ajax({
//         url: '/local/ajax/mail-itiso.php',
//         type: "post",
//         contentType: false,
//         processData: false,
//         data: formData,
//         success: function (res) {
//             let url = window.location.href;
//             url = url.split('?')[1].split('&')
//             location.href = '/?' + url[0]
//         }
//     });
// });
//
// $(document).on('click', '#answer-message', function (e) {
//     e.preventDefault();
//     var formData = new FormData();
//     formData.append('event', 'asnwer-message');
//     formData.append('message', $('.editor').html());
//     formData.append('subject', $('#subject-mail').val());
//     formData.append('user', $('#now-user').val());
//     formData.append('userTo', $('#answer-user-to').val());
//     $.ajax({
//         url: '/local/ajax/mail-itiso.php',
//         type: "post",
//         contentType: false,
//         processData: false,
//         data: formData,
//         success: function (res) {
//             let url = window.location.href;
//             url = url.split('?')[1].split('&')
//             location.href = '/?' + url[0]
//         }
//     });
// })

$(document).on('input', '.loginpage__form-field[name="REGISTER[EMAIL]"]', function () {
    $('.loginpage__form-field[name="REGISTER[LOGIN]"]').val($(this).val())
})

$(document).ready(function () {
    let val = $('.loginpage__form-field[name="REGISTER[EMAIL]"]').val()
    $('.loginpage__form-field[name="REGISTER[LOGIN]"]').val(val)

    // setInterval(function () {
    //     let countInbox = $('#countInbox').attr('data-count');
    //     if (countInbox === '') countInbox = 0
    //     let arMessage = []
    //     var all = $(".client__list-item").map(function () {
    //         arMessage.push($(this).attr('data-id'));
    //     }).get();
    //     var formData = new FormData();
    //     formData.append('event', 'update-message');
    //     formData.append('page', $('#nowpage').val());
    //     formData.append('arMessage', arMessage);
    //     var myAudio = new Audio;
    //     $.ajax({
    //         url: '/local/ajax/mail-itiso.php',
    //         type: "post",
    //         contentType: false,
    //         processData: false,
    //         data: formData,
    //         success: function (res) {
    //             let lastEl = arMessage[0]
    //             res = JSON.parse(res)
    //             $('#countInbox').text(res.inbox)
    //             if (countInbox < res.inbox) {
    //                 var audio = new Audio();
    //                 audio.src = '/local/templates/mail/send.mp3';
    //                 audio.autoplay = true;
    //                 let url = window.location.href;
    //                 url = url.split('?')[1].split('&')
    //                 if (url[0] === 'page=inbox') {
    //                     if (arMessage.length > 0) {
    //                         $(res.html).insertBefore('.client__list-item[data-id="' + lastEl + '"]')
    //                     } else {
    //                         $('.client__list').html(res.html)
    //                     }
    //                 }
    //                 $('#countInbox').attr('data-count', res.inbox)
    //             }
    //         }
    //     });
    // }, 5000);
})