{
  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixpkgs-unstable";

    chips.url = "github:jasonrm/nix-chips";
    chips.inputs.nixpkgs.follows = "nixpkgs";
  };

  outputs = {
    self,
    nixpkgs,
    chips,
    ...
  }:
    chips.lib.use {
      devShellsDir = ./nix/devShells;
      overlays = [
        (self: super: {
          php = self.php83.buildEnv {
            extensions = {
              enabled,
              all,
              ...
            }:
              with all;
                enabled
                ++ [
                  apcu
                  event
                  imagick
                  igbinary
                ];
            extraConfig = ''
              output_buffering = 4096
              post_max_size = 100M
              upload_max_filesize = 100M
              variables_order = EGPCS
            '';
          };
        })
      ];
    };
}
