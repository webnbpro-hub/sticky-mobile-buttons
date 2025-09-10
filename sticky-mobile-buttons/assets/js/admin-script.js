jQuery(document).ready(function($) {
    // Инициализация цветовых полей
    $('.smb-color-field').wpColorPicker();
    
    // Динамическое изменение полей в зависимости от типа кнопки
    $('.smb-button-type').change(function() {
        var type = $(this).val();
        var parent = $(this).closest('.smb-button-settings');
        
        // Обновляем label
        var labelText = '';
        switch(type) {
            case 'phone': labelText = smb_admin.labels.phone_number; break;
            case 'telegram': labelText = smb_admin.labels.telegram_username; break;
            case 'whatsapp': labelText = smb_admin.labels.whatsapp_number; break;
            case 'cart': labelText = smb_admin.labels.cart_url; break;
            case 'custom': labelText = smb_admin.labels.link_url; break;
        }
        parent.find('.smb-value-label').text(labelText);
        
        // Показываем/скрываем поле для кастомной иконки
        if (type === 'custom') {
            parent.find('.smb-custom-icon').show();
        } else {
            parent.find('.smb-custom-icon').hide();
        }
    });
    
    // Переключение между Font Awesome и кастомными иконками
    $('.smb-icon-type').change(function() {
        var type = $(this).val();
        var parent = $(this).closest('.smb-button-settings');
        
        if (type === 'fontawesome') {
            parent.find('.smb-fontawesome-icon').show();
            parent.find('.smb-custom-icon').hide();
        } else {
            parent.find('.smb-fontawesome-icon').hide();
            parent.find('.smb-custom-icon').show();
        }
    });
    
    // Обновление preview иконки Font Awesome
    $('.smb-icon-select').change(function() {
        var iconClass = $(this).val();
        $(this).siblings('.smb-icon-preview').find('i').attr('class', iconClass);
    });
    
    // Загрузка кастомных иконок
    $('.smb-upload-icon').click(function(e) {
        e.preventDefault();
        
        var button = $(this);
        var customIconField = button.siblings('.smb-custom-icon-id');
        var preview = button.siblings('.smb-custom-icon-preview');
        var removeButton = button.siblings('.smb-remove-icon');
        
        // Создаем медиафрейм
        var frame = wp.media({
            title: smb_admin.title,
            button: {
                text: smb_admin.button_text
            },
            multiple: false
        });
        
        // Обработка выбора изображения
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            customIconField.val(attachment.id);
            preview.html('<img src="' + attachment.url + '" style="max-width:50px;max-height:50px;">');
            removeButton.show();
        });
        
        // Открываем медиафрейм
        frame.open();
    });
    
    // Удаление кастомной иконки
    $('.smb-remove-icon').click(function() {
        var button = $(this);
        var customIconField = button.siblings('.smb-custom-icon-id');
        var preview = button.siblings('.smb-custom-icon-preview');
        
        customIconField.val(0);
        preview.empty();
        button.hide();
    });
    
    // Инициализация всех полей при загрузке страницы
    $('.smb-button-type').trigger('change');
    $('.smb-icon-type').trigger('change');
});