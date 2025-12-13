{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";

    chips.url = "github:jasonrm/nix-chips";
    chips.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = {chips, ...}:
    chips.lib.use {
      devShellsDir = ./nix/devShells;
    };
}
