<?php

use Botble\Base\Facades\Form;
use Botble\Base\Facades\MacroableModels;
use Botble\Base\Facades\MetaBox;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Forms\FormHelper;
use Botble\Media\Facades\RvMedia;
use Botble\RealEstate\Models\Facility;
use Botble\RealEstate\Models\Feature;
use Botble\RealEstate\Models\Project;
use Botble\RealEstate\Models\Property;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\Youtube;
use Illuminate\Support\Arr;
use Theme\FlexHome\Forms\Fields\ThemeIconField;

register_page_template([
    'homepage' => __('Homepage'),
    'full-width' => __('Full width'),
]);

register_sidebar([
    'id' => 'footer_sidebar',
    'name' => __('Footer sidebar'),
    'description' => __('Footer sidebar for Flex Home theme'),
]);

RvMedia::setUploadPathAndURLToPublic();

RvMedia::addSize('small', 410, 270);

add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function (FormAbstract $form, $data): FormAbstract {
    switch (get_class($data)) {
        case Facility::class:
        case Feature::class:

            $iconImage = null;
            if ($data->getKey()) {
                $iconImage = MetaBox::getMetaData($data, 'icon_image', true);
            }

            $form
                ->withCustomFields()
                ->modify('icon', 'themeIcon', ['label' => __('Font Icon')], true)
                ->addAfter('icon', 'icon_image', 'mediaImage', [
                    'value' => $iconImage,
                    'label' => __('Icon Image (It will replace Font Icon if it is present)'),
                ]);

            break;
    }

    return $form;
}, 127, 2);

function get_object_property_map(): object
{
    return (object)[
        'name' => '__name__',
        'status_html' => '__status_html__',
        'url' => '__url__',
        'city_name' => '__city_name__',
        'square_text' => '__square_text__',
        'number_bedroom' => '__number_bedroom__',
        'number_bathroom' => '__number_bathroom__',
        'image_thumb' => '__image_thumb__',
        'price' => '__price__',
        'price_html' => '__price_html__',
    ];
}

app()->booted(function () {
    if (is_plugin_active('real-estate')) {
        $videoSupportModels = [Project::class, Property::class];

        add_action(BASE_ACTION_META_BOXES, function ($context, $object) use ($videoSupportModels) {
            if (in_array(get_class($object), $videoSupportModels) && $context == 'advanced') {
                MetaBox::addMetaBox('additional_property_fields', __('Addition Information'), function () {
                    $videoThumbnail = null;
                    $videoUrl = null;
                    $args = func_get_args();
                    if (! empty($args[0])) {
                        $videoThumbnail = $args[0]->video_thumbnail;
                        $videoUrl = $args[0]->video_url;
                    }

                    return Theme::partial('additional-property-fields', compact('videoThumbnail', 'videoUrl'));
                }, get_class($object), $context);
            }
        }, 28, 2);

        add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($type, $request, $object) use ($videoSupportModels) {
            if (in_array(get_class($object), $videoSupportModels) && $request->has('video')) {
                $data = Arr::only((array)$request->input('video', []), ['url', 'thumbnail']);

                if ($request->hasFile('thumbnail_input')) {
                    $result = RvMedia::handleUpload($request->file('thumbnail_input'), 0, 'properties');
                    if (! $result['error']) {
                        $file = $result['data'];
                        $data['thumbnail'] = $file->url;
                    }
                }

                MetaBox::saveMetaBoxData($object, 'video', $data);
            }
        }, 280, 3);

        foreach ($videoSupportModels as $supportModel) {
            MacroableModels::addMacro($supportModel, 'getVideoThumbnailAttribute', function () {
                $this->loadMissing('metadata');

                return Arr::get($this->getMetaData('video', true) ?: [], 'thumbnail', '');
            });

            MacroableModels::addMacro($supportModel, 'getVideoUrlAttribute', function () {
                $this->loadMissing('metadata');

                return Arr::get($this->getMetaData('video', true) ?: [], 'url', '');
            });
        }
    }
});

function get_image_from_video_property(Property|Project $item): string
{
    if ($item->video_thumbnail) {
        return RvMedia::getImageUrl($item->video_thumbnail);
    }

    $videoID = Youtube::getYoutubeVideoID($item->video_url);

    if ($videoID) {
        return 'https://img.youtube.com/vi/' . $videoID . '/hqdefault.jpg';
    }

    return RvMedia::getDefaultImage();
}

add_filter('form_custom_fields', function (FormAbstract $form, FormHelper $formHelper) {
    if (! $formHelper->hasCustomField('themeIcon')) {
        $form->addCustomField('themeIcon', ThemeIconField::class);
    }

    return $form;
}, 29, 2);

Form::component('themeIcon', Theme::getThemeNamespace() . '::partials.forms.fields.icons-field', [
    'name',
    'value' => null,
    'attributes' => [],
]);

if (is_plugin_active('real-estate')) {
    add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($type, $request, $object) {
        if (in_array(get_class($object), [Facility::class, Feature::class]) && $request->has('icon_image')) {
            MetaBox::saveMetaBoxData($object, 'icon_image', $request->input('icon_image'));
        }
    }, 230, 3);
}
