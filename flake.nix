{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";
    utils.url = "github:numtide/flake-utils";
  };

  outputs = {
    self,
    nixpkgs,
    utils,
    ...
  }:
    utils.lib.eachDefaultSystem (
      system: let
        pkgs = import nixpkgs {
          inherit system;
          config = {};
          overlays = [];
        };

        php = pkgs.php83.buildEnv {
          extensions = {
            enabled,
            all,
            ...
          }:
            with all;
              enabled
              ++ [
                event
                imagick
              ];
        };
      in {
        devShell = pkgs.mkShell {
          buildInputs = with pkgs; [
            nodejs
            nodePackages.pnpm

            php
            php.packages.composer

            lefthook
            go-task
          ];
        };
      }
    );
}
