{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    chips.url = "github:jasonrm/nix-chips";
    chips.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = {chips, ...}:
    chips.lib.use {
      devShellsDir = ./nix/devShells;
    };
}
