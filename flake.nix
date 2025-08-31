{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-25.05";

    chips.url = "github:jasonrm/nix-chips";
    chips.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = {chips, ...}:
    chips.lib.use {
      devShellsDir = ./nix/devShells;
      overlays = [chips.overlays.default];
    };
}
