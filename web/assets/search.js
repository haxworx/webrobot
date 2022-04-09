function display_result(bot_id)
{
    url = '/display/?id=' + bot_id;
    document.getElementById('search_content').src = url;

    const input = document.getElementById('search_input');
    input.setSelectionRange(input.value.length, input.value.length);
}

