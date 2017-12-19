# Tests required

Note: look at thephplegue/flysystem package.

## File manager

- base object
    - create new with empty parameters
    - create new with wrong field -> exception
- alias config
    - create new with wrong parameters -> exception
- create check if all parameters set
- formatters config
    - try to call factory with wrong key
    - create new with wrong parameters -> exception
    - run formatter
- cache or upload file
    - get asset/upload path
    - check if it empty
    - cache/upload file/image
    - check if updated


## Save

- create new IFile
- save file into upload directory


## Cache

- create asset path
- copy file into cache
    - try to write into non-writable directory


## Formatter

- check all parameters
- new formatter build
- run formatters
- apply adapters
