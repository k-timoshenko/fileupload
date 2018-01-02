<?php
declare(strict_types=1);

namespace tkanstantsin\fileupload\formatter\icon;

/**
 * Class IconGenerator searches icon by file extension.
 */
class IconGenerator
{
    public const ICON_DEFAULT = 1;
    public const ICON_TEXT = 2;
    public const ICON_DOC = 3;
    public const ICON_EXCEL = 4;
    public const ICON_POWER_POINT = 5;
    public const ICON_PDF = 6;
    public const ICON_IMAGE = 7;
    public const ICON_ARCHIVE = 8;
    public const ICON_AUDIO = 9;
    public const ICON_VIDEO = 10;

    private const TYPE_TO_REGEX = [
        self::ICON_DEFAULT => null,
        // docs
        self::ICON_TEXT => 'txt',
        self::ICON_DOC => 'doc[x]?|odt',
        self::ICON_EXCEL => 'xls[xb]?|ods',
        self::ICON_POWER_POINT => 'ptt[x]?|odp',
        self::ICON_PDF => 'pdf',
        self::ICON_IMAGE => 'jpe?g|png|gif',
        // archives
        self::ICON_ARCHIVE => 'zip|rar|7zip',
        // multimedia
        self::ICON_AUDIO => 'mp3',
        self::ICON_VIDEO => 'mp4',
    ];

    /**
     * @var string
     */
    private $iconPrefix;
    /**
     * @var array
     */
    private $iconSet;

    /**
     * @param string $class
     * @return null|IconGenerator
     */
    public static function build($class): ?self
    {
        /* @var ElusiveIcons|FontAwesome $iconSetClass */
        $iconSetClass = $class ?? FontAwesome::class;

        return new self($iconSetClass::PREFIX, $iconSetClass::ICON_SET);
    }

    /**
     * IconGenerator constructor.
     * @param string $iconPrefix
     * @param array $iconSet
     */
    public function __construct(string $iconPrefix, array $iconSet)
    {
        $this->iconPrefix = $iconPrefix;
        $this->iconSet = $iconSet;

        $this->init();
    }

    /**
     * Initialize object
     */
    public function init(): void
    {
        // check if $iconSet implement all available icons from self::TYPE_TO_REGEX
        $diff1 = array_keys(\array_diff_assoc($this->iconSet, self::TYPE_TO_REGEX));
        $diff2 = array_keys(\array_diff_assoc(self::TYPE_TO_REGEX, $this->iconSet));
        if ($diff1 === $diff2) {
            \trigger_error('Icon set doesn\'t implement all range of icons.');
        }
    }

    /**
     * Returns suitable css class for icon by extension.
     * @param null|string $extension
     * @return string returns default icon if nothing found
     */
    public function getIcon(?string $extension): ?string
    {
        $iconClass = $this->iconSet[$this->getIconType($extension)] ?? null;

        return $iconClass !== null
            ? $this->iconPrefix . ' ' . $iconClass
            : null;
    }

    /**
     * Searches icon type by it's extension
     * @param null|string $extension
     * @return string
     */
    public function getIconType(?string $extension): string
    {
        if ($extension !== null && $extension !== '') {
            foreach (self::TYPE_TO_REGEX as $type => $regex) {
                if ($regex === null) {
                    continue;
                }
                if (($regex === $extension && preg_match('/[a-z0-9]+/', $extension))
                    || preg_match("/$regex/", $extension)
                ) {
                    return $type;
                }
            }
        }

        return self::ICON_DEFAULT;
    }
}