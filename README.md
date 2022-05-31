# Sliced Files Packer

[![Build Status](https://travis-ci.org/keboola/sliced-files-packer.svg?branch=master)](https://travis-ci.org/keboola/sliced-files-packer)

This component is used for sliced files download in KBC UI.
All parts of sliced files are added to ZIP package which is then uploaded back to Files Storage and offered for
download.

## Usage Example
Component expects one sliced file as input. Otherwise user error is returned.


```json
{
  "configData": {
   "storage": {
      "input": {
            "files": [
                {
                    "query": "id:323156728"
                }
            ]
        }
     }
  }
}
```

## Development


`docker-compose run --rm dev`
## License

MIT licensed, see [LICENSE](./LICENSE) file.
