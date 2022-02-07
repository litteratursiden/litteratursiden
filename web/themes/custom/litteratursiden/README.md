# Dokk two theme

## Quick guide

###install tools and requirements

#### Tested and working in:
Node: v14.17.4
Npm: 6.14.14

```sh
FONTAWESOME_NPM_AUTH_TOKEN='YOUR_FONTAWESOME_TOKEN' \
  NPM_CONFIG_USERCONFIG=.npmrc.install
```
Copy .npmrc.install to .npmrc and replace:
`"${FONTAWESOME_NPM_AUTH_TOKEN}"`
with a token from your fontawesome account.


### Install packages
The package.json file contains the versions of all the node packages you need. To install them run:
```sh
yarn install
```

### Build assets
Build for development:
```sh
yarn dev
```

Build for development and keep watching files:
```sh
yarn watch
```

Build for Production:
```sh
yarn build
```
